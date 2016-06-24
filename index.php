<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

class Config
{
    //set to true to enable debug: logging, error message,
    public static $debug = false;

    // include joined php files kernel.php; $debug = true disables kernel
    public static $useKernel = true;

    // enable logging
    public static $logLevel = false;

    // host name or false
    public static $host = false;

    //set to true to ignore request, engine will be initilized
    public static $ignoreRequest = false;

    //callback function before make request, if it enabled
    public static $beforeRequest = false;

    //random string to mix solt encrypt and generate passwords
    public static $secret = '8r7j7hbt8iik//pt7hUy5/e/7FQvVBoh7/Zt8sCg8+ibVBUt7rQ';

    //database config
    public static $db = false;

    /* you can configure database account here or
    public static $db = [
    // driver name not used, reserved for future
    'driver' => 'mysqli',
    'host' => 'localhost',
    // 0 to ignore
    'port' => 0,
    'dbname' => 'database_name',
    'login' => 'database_user',
    'password' => '***',
    //table names prefix
    'prefix' => 'prefix_'
    ];
    */

    //after connect to database remove from sql_mode values NO_ZERO_IN_DATE, NO_ZERO_DATE
    public static $enableZeroDatetime = false;

    // false | true | ['host' => '127.0.0.1', 'port' => 11211];
    public static $memcache = false;

    //replacement classes on startup
    public static $classes = [
    //'app' => '\litepubl\core\App',
    //'storage' => '\litepubl\core\StorageInc',
    //'cache' => '\litepubl\core\CacheFile,
    //'logmanager' => '\litepubl\debug\LogManager',
    ];

    //key = value for ini_set
    public static $phpIni = [];

    //not used, reservedfor future
    public static $extra = [];
}

if (!defined('litepubl_mode') || (litepubl_mode != 'config')) {
    if (defined('litepubl_mode') && (litepubl_mode == 'ignoreReqest')) {
        Config::$ignoreRequest = true;
    }

    if (Config::$debug || !Config::$useKernel) {
        require (__DIR__ . '/lib/debug/kernel.php');
    } else {
        require (__DIR__ . '/lib/core/kernel.php');
    }
}

