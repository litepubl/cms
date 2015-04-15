<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
litepublisher::$debug = true;
set_time_limit(120);

    $host = 'domain.ru';
    $login = 'login';
    $password = 'password';

    $backuper = tbackuper::instance();
    if (($host == '') || ($login == '') || ($password == '')) die('bad logon');
    if (!$backuper->connect($host, $login, $password)) die('not connetct');

    $updater = tupdater::instance();
$r = $updater->auto2(0.11 + litepublisher::$options->version);
var_dump($r, litepublisher::$options->version);
