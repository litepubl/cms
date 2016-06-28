<?php
namespace litepubl\update;
if (file_exists(__DIR__ . '/migrate.php')) {
require (__DIR__ . '/migrate.php');
} elseif(file_exists(__DIR__ . '/lib/update/update7/migrate.php')) {
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);

require (__DIR__ . '/lib/update/update7/migrate.php');
} else {
echo 'Migrate scripts not found';
return false;
}

migrate::run();