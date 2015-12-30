<?php
function update607() {
$classes = litepublisher::$classes;
unset($classes->items['poststatus']);
unset($classes->items['tadminthemes']);
$classes->save();
$m = tadminmenus::i();
$m->deleteurl('/admin/views/themes/');
}