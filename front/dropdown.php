<?php

// inc/dropdown_ticketflow.class.php

class Dropdown_Ticketflow extends Dropdown {
    public function show($itemtype, $options = []) {
        // Sobreescribimos el método show para no utilizar la columna entities_id
        $options['order'] = 'name'; // ordenamos por la columna name en lugar de entities_id
        return parent::show($itemtype, $options);
    }
}