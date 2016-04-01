<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class config {
//set to true to enable debug
public static $debug = false;

// host name or false
public static $host = false;

//die if invalid host name in current request. Set to false if use in command line mode
public static $dieOnInvalidHost = true;

//set to false to ignore request, cms will be initilized
public static $canRequest = true;

//callback function
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

  // false | true | array('host' => '127.0.0.1', 'port' => 11211);
  public static $memcache =  false;

//replacement classes on startup
public static $classes = [
//'root' => 'litepubl\litepubl',
//'storage' => 'litepubl\storage',
//'cache' => 'litepubl\cache',
];
}

if (config::$debug) {
require (__DIR__ . '/lib/kernel.debug.php');
} else {
require (__DIR__ . '/lib/kernel.php');
}