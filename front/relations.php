<?php
include('../../../inc/includes.php');

Html::header(
   __('Relaciones TicketFlow', 'ticketflow'),
   $_SERVER['PHP_SELF'],
   'plugins',
   'ticketflow',
   'relations'
);

$config = new PluginTicketflowRelations();
Session::checkRight('entity', READ);

// Botón "Añadir"
echo "<div class='justify-content-center d-flex gap-2'>";
echo "<a class='vsubmit' href='relations.form.php'>" . __('Añadir', 'ticketflow') . "</a>";
echo "<a class='vsubmit' href='config.form.php'>" . __('Configurar', 'ticketflow') . "</a>";
echo "</div><br>";
$config->rawSearchOptions();
$config->showList();

Html::footer();