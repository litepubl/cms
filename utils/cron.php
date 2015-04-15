<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);

define('litepublisher_mode', 'xmlrpc');
include('index.php');
litepublisher::$debug = true;
$cron = tcron::i();
$_GET['cronpass'] = $cron->password;
echo "<pre>\nmustbe start<br>";
flush();
var_dump($cron->request(null));
echo "finish";