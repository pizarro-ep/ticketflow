<?php
include('../../../inc/includes.php');

Html::header(
   __('Relaciones TicketFlow', PLUGIN_TICKETFLOW_DOMAIN),
   $_SERVER['PHP_SELF'],
   'plugins',
   PLUGIN_TICKETFLOW_DOMAIN,
   'relations'
);

$config = new PluginTicketflowRelations();
Session::checkRight('entity', READ);

// Botón "Añadir"
echo "<div class='justify-content-center d-flex gap-2'>";
echo "<a class='vsubmit' href='relations.form.php'>" . __('Añadir', PLUGIN_TICKETFLOW_DOMAIN) . "</a>";
echo "<a class='vsubmit' href='config.form.php'>" . __('Configurar', PLUGIN_TICKETFLOW_DOMAIN) . "</a>";
echo "</div><br>";
$config->rawSearchOptions();
$config->showList();

Html::footer();