<?php
namespace litepubl;

            error_reporting(-1);
            ini_set('display_errors', 1);
           Header('Cache-Control: no-cache, must-revalidate');
            Header('Pragma: no-cache');

if (!defined('litepubl\mode')) {
define('litepubl\mode', 'config');
}

require (__DIR__ . '/index.php');
Config::$debug = true;
//Config::$classes['storage'] = 'litepubl\core\storageinc';
Config::$afterInit = function($app) {
if (file_exists(__DIR__ . '/temp/zdebug.php')) {
include (__DIR__ . '/temp/zdebug.php');
}
};

include (__DIR__ . '/lib/utils/Filer.php');
$d = __dir__ . '/storage/data';
//utils\Filer::delete($d, true, false);
//utils\Filer::append(__DIR__ . '/storage/log.txt', '');
//echo "<pre>\n";
//flush();

try {
if (Config::$debug) {
require (__DIR__ . '/lib/debug/kernel.php');
} else {
require (__DIR__ . '/lib/core/kernel.php');
}
\litepubl\core\litepubl::$app->getLogger();
} catch (\Throwable $e) {
echo "<pre>\n";
echo $e;
}

return;
echo "<pre>\n";
echo gc_collect_cycles();
echo ltrim(str_replace(__DIR__, '', implode("\n", get_included_files())), '/\\');
echo "\n", count(get_included_files());