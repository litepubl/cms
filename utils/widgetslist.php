<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
echo "<pre>\n";
$widgets = twidgets::instance();
var_dump($widgets->data);
?>