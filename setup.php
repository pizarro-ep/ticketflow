<?php

include __DIR__ . '/inc/define.php';

function plugin_init_ticketflow()
{
   /** @var array $PLUGIN_HOOKS */
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['ticketflow'] = true;
   // Hook al crear un ticket
   $PLUGIN_HOOKS['item_add']['ticketflow'] = ['Ticket' => 'ticketflow_item_add_called'];
   $PLUGIN_HOOKS['item_update']['ticketflow'] = ['Ticket' => 'ticketflow_updateitem_called'];
   //$PLUGIN_HOOKS['item_delete']['ticketflow'] = ['Ticket' => 'ticketflow_item_delete_called'];
   $PLUGIN_HOOKS['item_purge']['ticketflow'] = ['Ticket' => 'ticketflow_item_purge_called'];

   // add link in plugin page
   $PLUGIN_HOOKS['config_page']['ticketflow'] = 'front/config.form.php';

   // add entry to configuration menu
   $PLUGIN_HOOKS['menu_toadd']['ticketflow'] = ['plugins' => 'PluginTicketflowMenu'];

   Plugin::registerClass('PluginTicketflowTicketFlow');
   Plugin::registerClass('PluginTicketflowrRelations');
   Plugin::registerClass('PluginTicketflowConfig');
   Plugin::registerClass('PluginTicketflowMenu');
}

function plugin_version_ticketflow()
{
   return [
      'name' => __('Ticket Flow', 'ticketflow'),
      'version' => PLUGIN_FIELDS_VERSION,
      'author' => 'Eusebio Pizarro',
      'homepage' => 'https://github.com/pizarro-ep',
      'license' => 'GPLv2+',
      'requirements' => [
         'glpi' => [
            'min' => PLUGIN_TICKETFLOW_MIN_GLPI_VERSION,
            'max' => PLUGIN_TICKETFLOW_MAX_GLPI_VERSION,
            'dev' => false, //Required to allow 9.2-dev
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

