<?php
$url = '/';
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);
define('litepublisher_mode', 'debug');
include('index.php');
litepublisher::$debug = true;

try {
litepublisher::$urlmap->request(strtolower($_SERVER['HTTP_HOST']), $url);
} catch (Exception $e) {
  litepublisher::$options->handexception($e);
}
litepublisher::$options->showerrors();

echo "<pre>\n";

var_dump(litepublisher::$urlmap->context);
var_dump(litepublisher::$urlmap->finditem($url));