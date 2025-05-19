# Ticket Flow
Plugin de GLPI que permite sincronizar automaticamente tickets con los pisos y la creación de tickets hijos de acuerdo a la categoría y el estado.

## Caracteristicas

* Sincroniza automaticamente los tickets con el piso al que pertenece el solicitante.
* Permite crear relaciones entre plantillas y categorias.
* Permite crear automaticamente tickets de acuerdo a una categoria y un estado.

## Instalacion

1. Clonar el repositorio en la carpeta `plugins` de GLPI.
2. Activar el plugin en la pestaña `Administration` > `Plugins`.
3. Configurar las opciones del plugin en la pestaña `Setup` > `Plugins` > `Ticket Flow`.

## Uso

1. Crear una plantilla de ticket con una categoria y un estado en la pestaña `Setup` > `Ticket templates`.
2. En la pestaña `Setup` > `Plugins` > `Ticket Flow` > `Añadir`, seleccionar la plantilla y la categoria que se desean relacionar.
3. En la pestaña `Setup` > `Plugins` > `Ticket Flow` > `Configurar`, seleccionar el campo de piso que se usará para sincronizar.
4. En la pestaña `Helpdesk` > `Tickets`, el plugin creara automaticamente un ticket con la informacion de la plantilla y la categoria seleccionadas y si hay alguna relación creada se disparará el evento creado nuevos tickets de acuerdo a la plantilla seleccionada.

## Licencia

Este plugin esta bajo la licencia GPLv2.
