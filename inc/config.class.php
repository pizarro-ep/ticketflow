<?php

class PluginTicketflowConfig extends CommonDBTM
{
    public static $rightname = 'config';

    public static function getTypeName($nb = 0)
    {
        return _n('TicketFlow', 'TicketFlows', $nb);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        return __('Configuración');
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addStandardTab(__CLASS__, $ong, $options);
        return $ong;
    }

    public function configure()
    {
        Config::setConfigurationValues(PLUGIN_TICKETFLOW_CONTEXT, ['container' => 0, 'field' => 0]);
    }

    public static function canCreate()
    {
        return self::canUpdate();
    }

    public static function canView()
    {
        return Session::haveRight('config', 1);
    }

    public static function canUpdate()
    {
        return Session::haveRight('config', 1);
    }

    public function processForm($action, $config)
    {
        if ($action === 'update' && PluginTicketflowTicketflow::canUpdate()) {
            $validationErrors = $this->validateData($_POST);
            if (empty($validationErrors)) {
                Config::setConfigurationValues(PLUGIN_TICKETFLOW_CONTEXT, ['container' => $_POST['container'], 'field' => $_POST['field']]);

                $message = __('Configuración actualizada correctamente', PLUGIN_TICKETFLOW_DOMAIN);
                $status = INFO;
            } else {
                $message = implode('<br>', $validationErrors);
                $status = ERROR;
            }
            Session::addMessageAfterRedirect($message, false, $status);
        } else {
            Html::displayRightError();
        }
    }

    public function validateData($data)
    {
        $errors = [];

        // Validar contenedor
        $container = new PluginFieldsContainer();
        if (!$container->getFromDB($data['container'])) {
            $errors[] = 'Seleccione un contenedor válido';
        }

        // Validar campo
        $field = new PluginFieldsField();
        if (!$field->getFromDB($data['field']) || $field->fields['plugin_fields_containers_id'] != $data['container']) {
            $errors[] = 'Seleccione un campo válido';
        }

        return $errors;
    }

    public function showForm($ID, $options = [])
    {
        $rand = mt_rand();

        $this->forceTable(Config::getTable());
        $this->initForm($ID);
        $this->showFormHeader($options);

        echo "<div class='card-header d-flex flex-wrap mx-n2 mt-n2 mb-4 align-items-stretch flex-grow-1'>";
        echo "<h3 class='card-title d-flex align-items-center ps-4'>";
        echo "<i class='fa-fw ti ti-settings fa-2x me-2'></i>";
        echo "<span>Configuración de Ticketflow (Pisos)</span>";
        echo "</h3>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<i>Configuración de sincronización de pisos</i>";
        echo "</div>";

        $values = getAllDataFromTable(PluginFieldsContainer::getTable());
        // Crear arreglo para el dropdown
        $dropdown_data = [0 => '-----'];
        foreach ($values as $value) {
            $dropdown_data[$value['id']] = $value['label'];
        }
        echo '<tr><td>' . __('Contenedor para sincronización de pisos') . ': </td>';
        echo "<td colspan='2'>";
        $container = Config::getConfigurationValue(PLUGIN_TICKETFLOW_CONTEXT, 'container') ?? 0;
        Dropdown::showFromArray('container', $dropdown_data, ['value' => $container, 'rand' => $rand]);
        echo Html::showToolTip("Contenedor de campos (Plugin Fields)", ['title' => 'dfssss', 'link' => Plugin::getWebDir('fields') . '/front/container.form.php']);
        echo '</td>';
        echo '</tr>';

        $values = getAllDataFromTable(PluginFieldsField::getTable(), ['plugin_fields_containers_id' => $container]);

        // Crear arreglo para el dropdown
        $dropdown_data2 = [0 => '-----'];
        foreach ($values as $value) {
            $dropdown_data2[$value['id']] = $value['label'];
        }

        $field = Config::getConfigurationValue(PLUGIN_TICKETFLOW_CONTEXT, 'field') ?? 0;
        echo '<tr>';
        echo '<td>' . __('Campo a usar para sincronización de pisos') . ': </td>';
        echo '<td id="plugin_fields_fields_' . $rand . '"';
        echo "<td colspan='2'>";
        Dropdown::showFromArray('field', $dropdown_data2, ['value' => $field]);
        echo Html::showToolTip("Campos del contenedor (Plugin Fields)", ['link' => Plugin::getWebDir('fields') . '/front/container.form.php?id=' . $container]);
        echo '<td>';
        Ajax::updateItemOnSelectEvent(
            "dropdown_container$rand",
            "plugin_fields_fields_$rand",
            '../ajax/fields.dropdown.php',
            ['id' => $ID, 'container_id' => '__VALUE__', 'rand' => $rand],
        );

        echo '</td>';
        echo '</tr>';

        $this->showFormButtons($options); // Agrega botones de cancelar, etc.
        Html::closeForm();

        return true;
    }
}
