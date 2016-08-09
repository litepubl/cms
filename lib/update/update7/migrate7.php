<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\update;
if (file_exists(__DIR__ . '/migrate.php')) {
    include __DIR__ . '/migrate.php';
} elseif(file_exists(__DIR__ . '/lib/update/update7/migrate.php')) {
      Header('Cache-Control: no-cache, must-revalidate');
      Header('Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING);
    ini_set('display_errors', 1);

    include __DIR__ . '/lib/update/update7/migrate.php';
} else {
    echo 'Migrate scripts not found';
    return false;
}

migrate::run();
