<?php
function update600() {
$mparser = tmediaparser::i();
if (!isset($mparser->data['midlewidth'])) {
    $mparser->data['midlewidth'] = 760;
    $mparser->data['midleheight'] = 570;
    $mparser->data['enablemidle'] = true;
$mparser->save();
}

$man = tdbmanager::i();
if (!$man->column_exists('files', 'midle')) {
$man->alter('files', "add midle int unsigned NOT NULL default '0' after parent");
$man->alter('files', 'add key (midle)');
}

}