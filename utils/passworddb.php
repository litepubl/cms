<?php
$password = "";
define('dbversion', false);
define('litepublisher_mode', 'xmlrpc');
try {
include('index.php');
    } catch (Exception $e) {
echo "error: ";
echo $e->GetMessage();
}
   litepublisher::$options->setdbpassword($password);
litepublisher::$options->savemodified();
echo "<pre>\n";
echo "$password\n<br>new password";