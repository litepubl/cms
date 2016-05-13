<?php
namespace litepubl;

            error_reporting(-1);
            ini_set('display_errors', 1);
            Header('Cache-Control: no-cache, must-revalidate');
            Header('Pragma: no-cache');

define('litepubl_mode', 'config');
require (__DIR__ . '/index.php');
Config::$debug = true;

Config::$classes['storage'] = 'litepubl\core\storageinc';
Config::$beforeRequest  = function() {
include (__DIR__ . '/temp/zdebug.php');
};

include (__DIR__ . '/lib/utils/Filer.php');
$d = __dir__ . '/storage/data';
//utils\Filer::delete($d, true, false);
//utils\Filer::append(__DIR__ . '/storage/log.txt', '');

if (Config::$debug) {
require (__DIR__ . '/lib/debug/kernel.php');
} else {
require (__DIR__ . '/lib/core/kernel.php');
}