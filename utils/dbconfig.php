<?php
define('dbversion', false);
define('litepublisher_mode', 'xmlrpc');
try {
include('index.php');
    } catch (Exception $e) {
echo "error: ";
echo $e->GetMessage();
}

$c = &litepublisher::$options->data['dbconfig'];
$c['dbname'] = 'database_name';
$c['login'] = 'database_login';
litepublisher::$options->setdbpassword('database_password');
litepublisher::$options->save();
litepublisher::$options->savemodified();
