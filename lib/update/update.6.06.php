<?php
function update606() {
$classes = litepublisher::$classes;
unset($classes->items['tadmincommoncomments']);
unset($classes->items['ttablecolumns']);
$classes->save();

tjsmerger::i()->add('default', '/js/litepubl/system/storage.min.js');
}