<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);

define('litepublisher_mode', 'xmlrpc');
include('index.php');
   $password = md5uniq();
   litepublisher::$options->changepassword($password);
litepublisher::$options->savemodified();
echo "<pre>\n";
echo litepublisher::$options->email;
echo "\n$password\n<br>new password";
