<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
    $updater = tupdater::instance();
$r = $updater->autoupdate();
var_dump($r);
?>