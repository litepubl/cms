<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);
define('litepubl\mode', 'ignoreReqest');
include('index.php');

echo "<pre>\n";
\litepubl\updater\Updater::i()->update();