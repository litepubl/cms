<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl {

class litepubl {
public static $cache;
  public static $classes;
  public static $db;
  public static $debug ;
  public static $domain;
  public static $microtime;
  public static $options;
  public static $paths;
  public static $secret;
  public static $site;
  public static $storage;
  public static $urlmap;

  public static function init() {
    static::$microtime = microtime(true);
static::$secret = '8r7j7hbt8iik//pt7hUy5/e/7FQvVBoh7/Zt8sCg8+ibVBUt7rQ';
static::$debug = config::$debug || (defined('litepublisher_mode') && (litepublisher_mode == 'debug'));
    static::$domain = static::getHost();
static::createAliases();
static::createInstances();
  }

public function createAliases() {
\class_alias(get_called_class(), 'litepublisher');
\class_alias('litepubl\storage', 'storage');
}

public static function createInstances() {
    static::$paths = new tpaths();
static::createStorage();
  static::$classes = tclasses::i();
  static::$options = toptions::i();
  static::$site = tsite::i();
  static::$db = tdatabase::i();
static::$cache = new cache();
  static::$urlmap = turlmap::i();
}

public static function createStorage() {
if (isset(config::$classes['storage']) && class_exists(config::$classes['storage'])) {
$classname = config::$classes['storage'];
  static::$storage = new $classname();
} else {
  static::$storage = new storage();
}

if (!static::$storage->installed) {
    require(static::$paths->lib . 'install/install.php');
//exit() in lib/install/install.php
}
}

public static function getHost() {
if (config::$host) {
return config::$host;
}

$host = isset($_SERVER['HTTP_HOST']) ? \strtolower(\trim($_SERVER['HTTP_HOST'])) : false;
    if ($host && \preg_match('/(www\.)?([\w\.\-]+)(:\d*)?/', $host, $m)) {
return $m[2];
}

if (config::$dieOnInvalidHost ) {
      die('cant resolve domain name');
}
}

public static function request() {
if (static::$debug) {
    error_reporting(-1);
    ini_set('display_errors', 1);
 Header( 'Cache-Control: no-cache, must-revalidate');
  Header( 'Pragma: no-cache');
}

if (config::$beforeRequest && \is_callable(config::$beforeRequest)) {
\call_user_func_array(config::$beforeRequest, []);
}

    return static::$urlmap->request(static::$domain, $_SERVER['REQUEST_URI']);
}

public static function run() {
try {
static::init();

if (config::$canRequest) {
static::request();
}
} catch(\Exception $e) {
  static::$options->handexception($e);
}

static::$options->savemodified();
static::$options->showerrors();
}

} //class

}