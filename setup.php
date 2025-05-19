<?php

include __DIR__ . '/inc/define.php';

function plugin_init_ticketflow()
{
   /** @var array $PLUGIN_HOOKS */
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant'][PLUGIN_TICKETFLOW_DOMAIN] = true;
   // Hook al crear un ticket
   $PLUGIN_HOOKS['item_add'][PLUGIN_TICKETFLOW_DOMAIN] = ['Ticket' => 'ticketflow_item_add_called'];
   $PLUGIN_HOOKS['item_update'][PLUGIN_TICKETFLOW_DOMAIN] = ['Ticket' => 'ticketflow_updateitem_called'];
   //$PLUGIN_HOOKS['item_delete'][PLUGIN_TICKETFLOW_DOMAIN] = ['Ticket' => 'ticketflow_item_delete_called'];
   $PLUGIN_HOOKS['item_purge'][PLUGIN_TICKETFLOW_DOMAIN] = ['Ticket' => 'ticketflow_item_purge_called'];

   // add link in plugin page
   $PLUGIN_HOOKS['config_page'][PLUGIN_TICKETFLOW_DOMAIN] = 'front/config.form.php';

   // add entry to configuration menu
   $PLUGIN_HOOKS['menu_toadd'][PLUGIN_TICKETFLOW_DOMAIN] = ['plugins' => 'PluginTicketflowMenu'];

   Plugin::registerClass('PluginTicketflowTicketFlow');
   Plugin::registerClass('PluginTicketflowRelation');
   Plugin::registerClass('PluginTicketflowConfig');
   Plugin::registerClass('PluginTicketflowMenu');
}

function plugin_version_ticketflow()
{
   return [
      'name' => __('Ticket Flow', PLUGIN_TICKETFLOW_DOMAIN),
      'version' => PLUGIN_TICKETFLOW_VERSION,
      'author' => 'Eusebio Pizarro',
      'homepage' => 'https://github.com/pizarro-ep',
      'license' => 'GPLv2+',
      'requirements' => [
         'glpi' => [
            'min' => PLUGIN_TICKETFLOW_MIN_GLPI_VERSION,
            'max' => PLUGIN_TICKETFLOW_MAX_GLPI_VERSION,
            'dev' => false,  
         ],
      ],
   ];
}

function plugin_ticketflow_check_prerequisites()
{
   $plugin = new Plugin();

   if (!$plugin->isInstalled('fields') || !$plugin->isActivated('fields')) {
      echo "<div class='center'><div class='error'>" .
         "El plugin <strong>Fields</strong> debe estar instalado y habilitado para que <strong>Ticket Flow</strong> funcione correctamente." .
         "</div></div>";
      return false;
   }

   return true;
}


function plugin_ticketflow_check_config($verbose = false)
{
   return true;
}

