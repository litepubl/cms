<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\install;
use litepubl\Config;
use litepubl\core\Litepubl;

 litepubl::$app->classes = tclasses::i();
 litepubl::$app->options = toptions::i();
 litepubl::$app->site = tsite::i();

if (!defined('litepublisher_mode')) {
    define('litepublisher_mode', 'install');
}

/*
if (Config::$debug) {
  require_once( litepubl::$app->paths->lib . 'filer.class.php');
  if (is_dir( litepubl::$app->paths->data)) tfiler::delete( litepubl::$app->paths->data, true, true);
}
*/

require_once (__DIR__ . '/Installer.php');
$installer = new Installer();
$installer->run();
     litepubl::$app->SharedStorage->saveModified();

    if (!empty( litepubl::$app->options->errorlog)) {
        echo  litepubl::$app->options->errorlog;
    }

exit();