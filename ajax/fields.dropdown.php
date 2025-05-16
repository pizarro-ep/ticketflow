<?php

include('../../../inc/includes.php');
header('Content-Type: text/html; charset=UTF-8');
Html::header_nocache();
Session::checkLoginUser();

$container_id = $_POST['container_id'] ?? 0;

$values = getAllDataFromTable(PluginFieldsField::getTable(), ['plugin_fields_containers_id' => $container_id]);

// Crear arreglo para el dropdown
$dropdown_data = [0 => '-----'];
foreach ($values as $value) {
    $dropdown_data[$value['id']] = $value['label'];
}

echo "<td colspan='2'>";
Dropdown::showFromArray(
    'field',
    $dropdown_data,
    [
        'value' => $field
    ]
);
echo Html::showToolTip("Campos del contenedor (Plugin Fields)", ['link' => Plugin::getWebDir('fields') . '/front/container.form.php?id=' . $container_id]);
echo '<td>';
