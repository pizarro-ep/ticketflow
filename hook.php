<?php

function ticketflow_item_add_called(CommonDBTM $item)
{
    $tf = new PluginTicketflowTicketflow();
    $tf->item = $item;

    // ACTUALIZAR PISO AL CREAR EL TICKET
    return $tf->updateTicketFloor();
}

function ticketflow_updateitem_called(CommonDBTM $item)
{
    $tf = new PluginTicketflowTicketflow();
    $tf->item = $item;

    // ACTUALIZAR PISO AL ACTUALIZAR TICKET
    $tf->updateTicketFloor();

    // Crear ticket desde template
    $tf->createTicketByTemplate();
}

function ticketflow_item_purge_called(CommonDBTM $item)
{
    $tf = new PluginTicketflowTicketflow();
    $tf->item = $item;

    return $tf->deleteFloorTicket();
}

function plugin_ticketflow_install()
{
    $plugin_fields = new Plugin();
    $plugin_fields->getFromDBbyDir('ticketflow');
    $version = $plugin_fields->fields['version'];

    $migration = new Migration($version);

    $class = PluginTicketflowTicketflow::class;
    if (method_exists($class, 'installBaseData')) {
        $class::installBaseData($migration, $version);
    }

    $migration->executeMigration();

    return true;
}

function plugin_ticketflow_uninstall()
{
    return PluginTicketflowTicketflow::uninstall();
}


function plugin_ticketflow_getAddSearchOptionsNew($itemtype)
{
    $sopt = [];

    if ($itemtype == 'ticketflow') { 
        $sopt['table'] = PluginTicketflowRelations::getTable();
        $sopt['field'] = 'name';
        $sopt['name'] = __('Flujo de tickets', 'ticketflow');
        $sopt['datatype'] = 'itemlink';
        $sopt['forcegroupby'] = true;
        $sopt['usehaving'] = true;
    }

    return $sopt;
}

