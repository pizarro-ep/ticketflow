<?php
include('../../../inc/includes.php');

Html::header(
   __('Relaciones TicketFlow', PLUGIN_TICKETFLOW_DOMAIN),
   $_SERVER['PHP_SELF'],
   'plugins',
   'pluginticketflowmenu',
   'ticketflowrelations'
);

$config = new PluginTicketflowRelations();
Session::checkRight('entity', READ); 
   
$config->rawSearchOptions();
$config->showList();

Html::footer();