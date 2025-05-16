<?php

/**
 * Class PluginTicketflowRelations
 */
class PluginTicketflowRelations extends CommonDBTM
{
    public static $rightname = 'ticketflow';

    public static function getTypeName($nb = 0)
    {
        return _n('TicketFlow', 'TicketFlows', $nb);
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



    public function processForm($action, $config)
    {
        if ($action === 'add' && $config->canCreate()) {
            $validationErrors = $this->validateData($_POST);
            if (empty($validationErrors)) {
                $result = $config->add($_POST);
                $message = $result ? __('Relación guardada correctamente', 'ticketflow') : __('Error al guardar la relación', 'ticketflow');
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
                $message = $result ? __('Relación actualizada correctamente', 'ticketflow') : __('Error al guardar la relación', 'ticketflow');
                $status = $result ? INFO : ERROR;
            } else {
                $message = implode('<br>', $validationErrors);
                $status = ERROR;
            }
            Session::addMessageAfterRedirect($message, false, $status);
        } elseif ($action === 'purge' && $config->canPurge()) {
            $result = $config->delete($_POST);
            $message = $result ? __('Relación eliminada correctamente', 'ticketflow') : __('Error al eliminar la relación', 'ticketflow');
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
        if (!is_numeric($data['template_id']) || $data['template_id'] <= 0)
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
            'name' => 'template_id',
            'value' => $this->fields["template_id"]
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
        $options = [
            'candel' => $ID > 0,
            'addbuttons' => [
                'ss ' => [
                    'text' => __('Eliminar personalizado'),
                    'icon' => 'ti ti-trash', // opcional
                    'class' => 'btn btn-danger me-2' // clase Bootstrap para botón rojo
                ]
            ]
        ];
        $this->showFormButtons([
            'candel' => true,
            'canedit' => true,
        ]);


        Html::closeForm();

        return true;
    }

    public function showList()
    {
        global $DB;

        $result = $DB->request([
            "SELECT" => [
                "glpi_plugin_ticketflow_relations.*",
                "glpi_itilcategories.name AS category_name",
                "glpi_tickettemplates.name AS template_name"
            ],
            "FROM" => "glpi_plugin_ticketflow_relations",
            "LEFT JOIN" => [
                "glpi_itilcategories" => ["FKEY" => ["glpi_plugin_ticketflow_relations" => "itilcategories_id", "glpi_itilcategories" => "id"]],
                "glpi_tickettemplates" => ["FKEY" => ["glpi_plugin_ticketflow_relations" => "template_id", "glpi_tickettemplates" => "id"]]
            ]
        ]);

        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr class='noHover'><th>ID</th><th>Nombre</th><th>Categoría ITIL</th><th>PlantillaTicket</th><th>Estado</th></tr>";

        foreach ($result as $row) {
            $edit_url = "relations.form.php?id=" . $row['id'];
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td><a class='py-3' href='$edit_url'>" . $row['name'] . "</a></td>";
            echo "<td>" . $row['category_name'] . "</td>";
            echo "<td>" . $row['template_name'] . "</td>";
            echo "<td>" . Ticket::getStatusIcon($row['status']) . " " . Ticket::getStatus($row['status']) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
        echo "</div>";
    }
}
