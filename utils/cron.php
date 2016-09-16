<?php
namespace litepubl;
include 'head.php';

Config::$debug = true;
$cron = core\Cron::i();
$_GET['cronpass'] = $cron->password;
echo "<pre>\nmustbe start<br>";
flush();
var_dump($cron->request(null));
echo "finish";