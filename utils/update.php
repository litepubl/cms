<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);
define('litepubl\mode', 'ignoreReqest');
include('index.php');

echo "<pre>\n";

$file = __DIR__ . '/plugins/photoswipeThumbnail/PhotoSwwipeThumbnail.php';
//echo "$file\n";
var_dump(file_exists($file));
//var_dump(file_exists($file));
include $file;
$p = \litepubl\plugins\photoswipeThumbnail\PhotoSwwipeThumbnail::i();

\litepubl\updater\Updater::i()->run(7.02);