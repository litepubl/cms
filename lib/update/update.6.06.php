<?php
function update606() {
$classes = litepublisher::$classes;
unset($classes->items['tadmincommoncomments']);
$classes->save();
}