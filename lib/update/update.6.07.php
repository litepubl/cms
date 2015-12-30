<?php
function update607() {
$classes = litepublisher::$classes;
unset($classes->items['poststatus']);

$classes->save();
}