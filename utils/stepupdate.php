<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
    $updater = tupdater::instance();
$r = $updater->auto2(litepublisher::$options->version + 0.1);
var_dump($r);
?>