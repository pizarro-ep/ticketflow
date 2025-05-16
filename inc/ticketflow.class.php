<?php

class PluginTicketflowTicketflow extends CommonDBTM
{
    public static $rightname = 'ticketflow';
    public $item = null;
    private $container = null;
    private $field = null;
    private $fieldContainerClass = null;
    public $ticket_templates = 0;

    public static function getTypeName($nb = 0)
    {
        return _n('TicketFlow', 'TicketFlows', $nb);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        return __('Configuración');
    }

    public static function canCreate()
    {
        return Session::haveRight('config', 1);
    }

    public static function canView()
    {
        return Session::haveRight('config', 1);
    }

    public static function canUpdate()
    {
        return Session::haveRight('config', 1);
    }

    public static function canDelete()
    {
        return Session::haveRight('config', 1);
    }

    public static function canPurge()
    {
        return Session::haveRight('config', 1);
    }

    public function rawSearchOptions()
    {
        $options = [];

        $options[] = [
            'id' => 'common',
            'name' => __('Characteristics')
        ];

        $options[] = [
            'id' => 1,
            'table' => self::getTable(),
            'field' => 'name',
            'name' => __('Name'),
            'datatype' => 'itemlink'
        ];

        $options[] = [
            'id' => 2,
            'table' => self::getTable(),
            'field' => 'id',
            'name' => __('ID')
        ];

        $options[] = [
            'id' => 3,
            'table' => self::getTable(),
            'field' => 'id',
            'name' => __('Number of associated assets', 'myplugin'),
            'datatype' => 'count',
            'forcegroupby' => true,
            'usehaving' => true,
            'joinparams' => [
                'jointype' => 'child',
            ]
        ];


        return $options;
    }

    public function initialize()
    {
        $container_id = Config::getConfigurationValue(PLUGIN_TICKETFLOW_CONTEXT, 'container');
        $pfc = new PluginFieldsContainer();
        if ($pfc->getFromDB($container_id)) {
            $this->container = $pfc;
            $class = PluginFieldsContainer::getClassname(Ticket::getType(), $this->container->fields['name']);

            if (!class_exists($class)) {
                eval ("class $class extends CommonDBTM {}");
            }

            $this->fieldContainerClass = new $class;
        }

        $field_id = Config::getConfigurationValue(PLUGIN_TICKETFLOW_CONTEXT, 'field');
        $pff = new PluginFieldsField();
        if ($pff->getFromDB($field_id)) {
            $this->field = $pff;
        }
    }

    public function updateTicketFloor()
    {
        if ($this->item::getType() !== Ticket::getType())
            return false;

        $this->initialize(); // Inicializar contenedor y campo

        $ticketContainerInstance = $this->fieldContainerClass; // Instanciar contenedor
        if (!$ticketContainerInstance)
            return false; // Terminar el flujo si no se encuentra el contenedor

        if (!$this->field || $this->field->fields['type'] !== 'dropdown')
            return false; // Terminar el flujo is el campo no es de tipo dropdown

        // Verificar si el campo de piso aun no ha sido llenado
        if (count($ticketContainerInstance->find(['items_id' => $this->item->getID(), 'itemtype' => Ticket::getType(), 'plugin_fields_containers_id' => $this->container->getID(), $this->getNameFieldDropdownId() => ['>', 0]])) > 0)
            return false;

        $current_ticket = new Ticket();
        $current_ticket->getFromDB($this->item->getID());
        $actors = $current_ticket->getActorsForType(1); // Solo los actores solicitantes

        $floor = null;
        foreach ($actors as $actor) {
            if (isset($actor['items_id'])) {
                $user = new User();
                // Verificar que exista usuario con el id en cuestión
                if ($user->getFromDB($actor['items_id'])) {
                    if (empty($this->item->fields['locations_id']) && !empty($user->fields['locations_id'])) {
                        $this->item->fields['locations_id'] = $user->fields['locations_id'];
                        $current_ticket->update($this->item->fields);
                    }
                    if (!empty($user->fields['comment'])) {
                        $floor = trim($user->fields['comment']); // Obtener el piso del perfil del solicitante (campo 'comment')
                        break; // Finalizar blucle al encontrar el piso para el solicitante
                    }
                }
            }
        }

        if (empty($floor)) // Terminar el flujo si no se pudo obtener el piso del solicitante
            return false;

        $classname = PluginFieldsDropdown::getClassname($this->field->fields['name']);
        if (empty($classname))
            return false; // Terminar el flujo si no se pudo obtener el classname del dropdown
        $dropdown = new $classname(); // Instanciar el dropdown

        // Verificar si el piso obtenido ya existe en el dropdown 1. Existe: Obtener ID. 2. No existe: Agregar y obtener ID
        if ($dropdown->getFromDBByRequest(['WHERE' => ['name' => $floor], 'LIMIT' => 1])) {
            $floor_id = $dropdown->getID();
        } else {
            $dropdown_items = ['name' => $floor, 'completename' => $floor, 'level' => 1, 'comment' => 'Piso obtenido del perfil del usuario por TicketFlow'];
            $floor_id = $dropdown->add($dropdown_items);
        }

        if (empty($floor_id))
            return false; // Terminar el flujo si no se pudo obtener el ID del piso

        // Verificar si el piso ya ha sido asignado al ticket 1. Existe: Actualizar. 2. No existe: Agregar
        if ($ticketContainerInstance->getFromDBByCrit(['items_id' => $this->item->getID(), 'itemtype' => $this->item::getType(), 'plugin_fields_containers_id' => $this->container->getID()])) {
            $ticketContainerInstance->fields[$this->getNameFieldDropdownId()] = $floor_id;
            $ticketContainerInstance->update($ticketContainerInstance->fields);
        } else {
            $ticketContainerInstance->add([
                'items_id' => $this->item->getID(),
                'itemtype' => Ticket::getType(),
                'plugin_fields_containers_id' => $this->container->getID(),
                'entities_id' => $this->item->fields['entities_id'],
                $this->getNameFieldDropdownId() => $floor_id
            ]);
        }

        Session::addMessageAfterRedirect(__('Piso asignado al ticket de forma exitosa', 'ticketflow'), true);
        return true;
    }

    private function getNameFieldDropdownId()
    {
        return sprintf('plugin_fields_%sdropdowns_id', $this->field->fields['name']);
    }


    public function deleteFloorTicket()
    {
        $ticketContainerInstance = $this->fieldContainerClass;
        if (!$ticketContainerInstance)
            return false;

        $ticketContainerInstance->deleteByCriteria(['items_id' => $this->item->getID(), 'itemtype' => Ticket::getType(), 'plugin_fields_containers_id' => $this->container->getID()]);
    }


    public function createTicketByTemplate()
    {
        // Verificar si la plantilla existe para la categoría seleccionada
        if (!$this->existTicketTemplate())
            return false;

        // Obtener la cantidad de tickets hijos que se pueden crear con la plantilla seleccionada
        $count_ticket_templates = count($this->ticket_templates);
        foreach ($this->ticket_templates as $value) {
            $ticket_template = new TicketTemplate();
            if ($ticket_template->getFromDB($value['template_id'])) {
                if (!$this->canCreateTicketChild($count_ticket_templates, $value['status']))
                    continue; // Pasar al siguiente template si no se puede crear con este template

                // Obtener todos los campos predefinidos de la plantilla relacionada a la categoría seleccionada
                $ttpf = new TicketTemplatePredefinedField();
                $fields = $ttpf->find(['tickettemplates_id' => $ticket_template->getID()]);
                $template_fields = TicketTemplate::getAllowedFields();

                // Mapear los campos predefinidos con los campos del ticket
                $field_to_add = [];
                foreach ($fields as $field) {
                    if (array_key_exists($field['num'], $template_fields)) {
                        $field_to_add[$template_fields[$field['num']]] = $field['value'];
                    } else {
                        if ($field['num'] == 7)
                            $field_to_add['itilcategories_id'] = $field['value'];
                        if ($field['num'] == 14)
                            $field_to_add['type'] = $field['value'];
                    }
                }
                if (isset($field_to_add['itilcategories_id']) && $field_to_add['itilcategories_id'] == $this->item->fields['itilcategories_id']) 
                    continue; // Evitar crear ticket hijo con misma categoría del ticket padre


                unset($field_to_add['date']); // No se puede copiar la fecha de creación

                // Crear el ticket desde la plantilla
                $new_ticket = new Ticket();
                $id_added = $new_ticket->add($field_to_add);

                if ($id_added) { // Crear relacion del ticket padre (actual) y el ticket hijo (creado)
                    $this->addRelationTicketByTicket($this->item->getID(), $id_added);
                    Session::addMessageAfterRedirect(__('Ticket hijo creado de forma exitosa', 'ticketflow'), true);
                }
            }
        }

        return true;
    }

    public function existTicketTemplate()
    {
        $itilcategories_id = $this->item->fields['itilcategories_id'];

        $ptr = new PluginTicketflowRelations();
        $relations = $ptr->find(['itilcategories_id' => $itilcategories_id], []);
        if (count($relations) === 0) {
            return false;
        }
        $this->ticket_templates = array_values($relations);
        return true;
    }
    public function canCreateTicketChild($count_ticket_templates, $status = null): bool
    {
        if ($this->item->fields['status'] !== $status)
            return false;

        // Verificar si se puede crear ticket hijo 
        $tt = new Ticket_Ticket();
        return count($tt->find(['tickets_id_2' => $this->item->getID(), 'link' => Ticket_Ticket::SON_OF])) < $count_ticket_templates;
    }


    private function addRelationTicketByTicket($parent_id, $child_id)
    {
        $s = new Ticket_Ticket();
        return $s->add(['tickets_id_1' => $child_id, 'tickets_id_2' => $parent_id, 'link' => Ticket_Ticket::SON_OF]);
    }


    /**
     * Summary of installBaseData
     * @param Migration $migration
     * @param mixed $version
     * @return bool
     */
    public static function installBaseData(Migration $migration, $version)
    {
        /** @var DBmysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = PluginTicketflowRelations::getTable();

        if (!$DB->tableExists($table)) {
            $migration->displayMessage(sprintf(__('Installing %s'), $table));

            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                  `id`                          INT                 {$default_key_sign} NOT NULL AUTO_INCREMENT,
                  `name`                        VARCHAR(255)        NOT NULL,
                  `itilcategories_id`           INT                 UNSIGNED NOT NULL,
                  `template_id`                 INT                 UNSIGNED NOT NULL,
                  `status`                      INT                 NOT NULL,
                  `created_at`                  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at`                  TIMESTAMP           NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `itilcategories_id_template_id` (`itilcategories_id`, `template_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=$default_charset COLLATE=$default_collation ROW_FORMAT=DYNAMIC;";
            $DB->doQuery($query) or die($DB->error());
        }

        if (Config::getConfigurationValue(PLUGIN_TICKETFLOW_CONTEXT, 'container') === null) {
            $migration->addConfig(['container' => '0'], PLUGIN_TICKETFLOW_CONTEXT);
        }
        if (Config::getConfigurationValue(PLUGIN_TICKETFLOW_CONTEXT, 'field') === null) {
            $migration->addConfig(['field' => '0'], PLUGIN_TICKETFLOW_CONTEXT);
        }
        if (Config::getConfigurationValue(PLUGIN_TICKETFLOW_CONTEXT, 'type') === null) {
            $migration->addConfig(['type' => ''], PLUGIN_TICKETFLOW_CONTEXT);
        }

        return true;
    }

    /**
     * Summary of uninstall
     * @return bool
     */
    public static function uninstall()
    {
        /** @var DBmysql $DB */
        global $DB;

        $table = PluginTicketflowRelations::getTable();
        if ($DB->tableExists($table))
            $DB->doQuery("DROP TABLE IF EXISTS `{$table}`");

        $table = PluginTicketflowConfig::getTable();
        if ($DB->tableExists($table))
            $DB->doQuery("DROP TABLE IF EXISTS `{$table}`");

        $table = PluginTicketflowRelations::getTable();
        if ($DB->tableExists($table))
            $DB->doQuery("DROP TABLE IF EXISTS `{$table}`");

        return true;
    }
}
