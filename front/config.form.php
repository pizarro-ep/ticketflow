<?php
include(__DIR__ . '/../../../inc/includes.php');

$config = new PluginTicketflowConfig();

// Procesar la acción (add o update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config->processForm('update', $config);
    Html::redirect($_SERVER['PHP_SELF']);
}

// Mostrar formulario
Html::header(
    __('Configuración | Ticketflow', PLUGIN_TICKETFLOW_DOMAIN),
    $_SERVER['PHP_SELF'],
    'plugins',
    PLUGIN_TICKETFLOW_DOMAIN,
    'config',
    false
);


$config->showForm( 1, ['formtitle' => 'Configuración de Ticketflow (Pisos)']);
//Html::back(); 

Html::footer();
?>