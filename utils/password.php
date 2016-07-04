<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);

define('litepubl\mode', 'config');
include('index.php');

   $password = md5uniq();
   litepubl::$app->options->changepassword($password);
   litepubl::$app->poolStorage->commit();
echo "<pre>\n";
echo litepubl::$app->options->email;
echo "\n$password\n<br>new password";
