<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\install;

use litepubl\Config;
use litepubl\core\Classes;
use litepubl\core\Litepubl;
use litepubl\core\Options;
use litepubl\core\Site;
use litepubl\utils\Filer;

//echo "<pre>\n";
if (!defined('litepublisher_mode')) {
    define('litepublisher_mode', 'install');
}
/*
if (false && Config::$debug) {
  require_once(dirname(__DIR__) . '/utils/Filer.php');
  if (is_dir( litepubl::$app->paths->data)) {
Filer::delete( litepubl::$app->paths->data, true, true);
}
}
*/

require_once(__DIR__ . '/Installer.php');
$installer = new Installer();
$installer->run();
litepubl::$app->poolStorage->commit();

if (!empty(litepubl::$app->options->errorlog)) {
    echo litepubl::$app->options->errorlog;
}

exit();
