<?php

/**
 * Class PluginTicketflowRelation
 */
class PluginTicketflowRelation extends CommonDBTM
{
    public static $rightname = PLUGIN_TICKETFLOW_NAME;

    public static function getTypeName($nb = 0)
    {
        return __('Relación', PLUGIN_TICKETFLOW_DOMAIN);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        return __('Relación Plantilla - Categoría');
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addStandardTab(__CLASS__, $ong, $options);
        return $ong;
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

    public function can($ID, $right, ?array &$input = null)
    {
        if ($right === DELETE) {
            return static::canDelete();
        }

        if ($right === UPDATE) {
            return static::canUpdate();
        }

        if ($right === READ) {
            return static::canView();
        }
        if ($right === CREATE) {
            return static::canCreate();
        }
        if ($right === PURGE) {
            return static::canDelete();
        }

        return false;
    }

    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __('Características generales')
        ];

        $tab[] = [
            'id' => 1,
            'table' => self::getTable(),
            'field' => 'name',
            'name' => __('Nombre'),
            'datatype' => 'itemlink',
            'massiveaction' => false,
        ];

        $tab[] = [
            'id' => 2,
            'table' => ITILCategory::getTable(),
            'field' => 'name',
            'name' => __('Categoría', 'ticketflow'),
            'datatype' => 'itemlink',
            'itemtype' => 'ITILCategory',
        ];

        $tab[] = [
            'id' => 3,
            'table' => ITILCategory::getTable(),
            'field' => 'completename',
            'name' => __('Nombre Completo de Categoría', 'ticketflow'),
            'datatype' => 'itemlink',
            'itemtype' => 'ITILCategory',
        ];

        $tab[] = [
            'id' => 4,
            'table' => TicketTemplate::getTable(),
            'field' => 'name',
            'name' => __('Plantilla', 'ticketflow'),
            'datatype' => 'itemlink',
            'itemtype' => 'TicketTemplate',
        ];
        $tab[] = [
            'id' => 5,
            'table' => self::getTable(),
            'field' => 'status',
            'name' => __('Estado'),
            'searchtype' => 'equals',
            'datatype' => 'specific'
        ];

        return $tab;
    }

    static function getSpecificValueToDisplay($field, $values, array $options = array())
    {

        if (!is_array($values)) {
            $values = array($field => $values);
        }
        switch ($field) {
            case 'status':
                return Ticket::getStatusIcon($values[$field]) . " " . Ticket::getStatus($values[$field]);

        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = array())
    {

        if (!is_array($values)) {
            $values = array($field => $values);
        } 

        switch ($field) {
            case 'status':
                $options['name'] = $name;
                $options['value'] = $values[$field];
                return Ticket::dropdownStatus($options);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }



    public function processForm($action, $config)
    {
        if ($action === 'add' && $config->canCreate()) {
            $validationErrors = $this->validateData($_POST);
            if (empty($validationErrors)) {
                $result = $config->add($_POST);
                $message = $result ? __('Relación guardada correctamente', PLUGIN_TICKETFLOW_DOMAIN) : __('Error al guardar la relación', PLUGIN_TICKETFLOW_DOMAIN);
                $status = $result ? INFO : ERROR;
            } else {
                $message = implode('<br>', $validationErrors);
                $status = ERROR;
            }
            Session::addMessageAfterRedirect($message, false, $status);
        } elseif ($action === 'update' && $config->canUpdate()) {
            $validationErrors = $this->validateData($_POST);
            if (empty($validationErrors)) {
                $result = $config->update($_POST);
                $message = $result ? __('Relación actualizada correctamente', PLUGIN_TICKETFLOW_DOMAIN) : __('Error al guardar la relación', PLUGIN_TICKETFLOW_DOMAIN);
                $status = $result ? INFO : ERROR;
            } else {
                $message = implode('<br>', $validationErrors);
                $status = ERROR;
            }
            Session::addMessageAfterRedirect($message, false, $status);
        } elseif ($action === 'purge' && $config->canPurge()) {
            $result = $config->delete($_POST);
            $message = $result ? __('Relación eliminada correctamente', PLUGIN_TICKETFLOW_DOMAIN) : __('Error al eliminar la relación', PLUGIN_TICKETFLOW_DOMAIN);
            $status = $result ? INFO : ERROR;
        } else {
            Html::displayRightError();
        }
    }

    public function validateData($data)
    {
        $errors = [];
        // Validar nombre
        if (empty($data['name']))
            $errors[] = 'El campo nombre es requerido.';
        // Validar template
        if (!is_numeric($data['tickettemplates_id']) || $data['tickettemplates_id'] <= 0)
            $errors[] = 'Seleccione una plantilla válida.';
        // Validar category
        if (!is_numeric($data['itilcategories_id']) || $data['itilcategories_id'] <= 0)
            $errors[] = 'Seleccione una categoría válida.';
        if (!is_numeric($data['status']) || $data['status'] <= 0 || $data['status'] > 6)
            $errors[] = 'Seleccione un estado válido.';
        return $errors;
    }


    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);
        // Mostrar encabezado del formulario (navegación con flechas)
        $this->showFormHeader($options); // Esto mostrará las flechas de navegación de GLPI

        if ($ID > 0) {
            echo "<div class='card-header d-flex flex-wrap mx-n2 mt-n2 mb-4 align-items-stretch flex-grow-1'>";
            echo "<h3 class='card-title d-flex align-items-center ps-4'>";
            echo "<i class='fa-fw ti ti-link fa-2x me-2'></i>";
            echo "<span>Relación de Ticketflow (Categoría - Plantilla)</span>";
            echo "</h3>";
            echo "</div>";
        }
        echo "<div class='card-body'>";
        echo "<i>Relación de Ticketflow entre Categoría y Plantilla</i>";
        echo "</div>";

        // Campo de texto: name
        echo "<tr>";
        echo "<td>" . __('Nombre') . "</td><td>";
        echo Html::input("name", ["value" => $this->fields['name']]);
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . __('Categoría ITIL') . "</td>";
        echo "<td>";
        Dropdown::show('ITILCategory', [
            'name' => 'itilcategories_id',
            'value' => $this->fields["itilcategories_id"]
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . __('Plantilla de ticket') . "</td>";
        echo "<td>";
        Dropdown::show('TicketTemplate', [
            'name' => 'tickettemplates_id',
            'value' => $this->fields["tickettemplates_id"]
        ]);
        echo "</>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . __('Estado de ticket') . "</td>";
        echo "<td>";
        Ticket::dropdownStatus([
            'name' => 'status',
            'value' => $this->fields["status"]
        ]);
        echo "</td>";
        echo "</tr>";
        $this->showFormButtons($options);


        Html::closeForm();

        return true;
    }
}
