<?php
function update592() {
  litepublisher::$site->jqueryui_version = '1.11.1';
  litepublisher::$site->save();

$db = tmetapost::i()->db;
    $db->delete("name = 'pinged'");
}