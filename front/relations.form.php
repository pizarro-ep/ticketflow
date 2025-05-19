<?php
include(__DIR__ . '/../../../inc/includes.php');

$relations = new PluginTicketflowRelations();

// Procesar la acción (add o update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $relations->processForm('add', $relations);
    } elseif (isset($_POST['update'])) {
        $relations->processForm('update', $relations);
    } elseif (isset($_POST['purge'])) {
        $relations->processForm('purge', $relations);
        Html::redirect($_SERVER['PHP_SELF']);
    }
    Html::redirect($_SERVER['PHP_SELF'] . (isset($_POST['id']) ? '?id=' . $_POST['id'] : ''));
}

// Mostrar formulario
Html::header(
    __('Relación de Ticketflow', PLUGIN_TICKETFLOW_DOMAIN),
    $_SERVER['PHP_SELF'],
    'plugins',
    'pluginticketflowmenu',
    'ticketflowrelations',
);

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $relations->getFromDB($_GET['id']);
}

$relations->showForm($_GET['id'] ?? 0, ['formtitle' => 'Relación de Ticketflow (Categoría - Plantilla)']);

Html::footer();
?>