<?php
include(__DIR__ . '/../../../inc/includes.php');

$relation = new PluginTicketflowRelation();

// Procesar la acción (add o update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $relation->processForm('add', $relation);
    } elseif (isset($_POST['update'])) {
        $relation->processForm('update', $relation);
    } elseif (isset($_POST['purge'])) {
        $relation->processForm('purge', $relation);
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
    'ticketflowrelation',
);

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $relation->getFromDB($_GET['id']);
}

$relation->showForm($_GET['id'] ?? 0, ['formtitle' => 'Relación de Ticketflow (Categoría - Plantilla)']);

Html::footer();
?>