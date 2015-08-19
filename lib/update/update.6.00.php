<?php
function update600() {
$mparser = tmediaparser::i();
if (!isset($mparser->data['midlewidth'])) {
    $mparser->data['midlewidth'] = 760;
    $mparser->data['midleheight'] = 570;
    $mparser->data['enablemidle'] = true;
$mparser->save();
}


}