<?php
include('../../../inc/includes.php');

Html::header(
   __('Relaciones TicketFlow', PLUGIN_TICKETFLOW_DOMAIN),
   $_SERVER['PHP_SELF'],
   'plugins',
   'pluginticketflowmenu',
   'ticketflowrelation'
);

$relation = new PluginTicketflowRelation();
Session::checkRight('entity', READ); 
   
$relation->rawSearchOptions();
Search::show('PluginTicketflowRelation'); 

Html::footer();