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
    if (defined('litepublisher_mode') && (litepublisher_mode == 'debug')) {
static::$debug = true;
}

    static::$domain = static::getHost();
static::createInstances();
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
  static::$storage = new storage();
if (!static::$storage->installed) {
    require(static::$paths->lib . 'install/install.php');
//exit() in lib/install/install.php
}
}

public static function getHost() {
if (config::$host) {
return config::$host;
}

$host = \isset(\$_SERVER['HTTP_HOST']) ? \strtolower(\trim(\$_SERVER['HTTP_HOST'])) : false;
    if ($host && \preg_match('/(www\.)?([\w\.\-]+)(:\d*)?/', $host, $m)) {
return $m[2];
}

if (config::die) {
      die('cant resolve domain name');
    }
}

public static function request() {
    return static::$urlmap->request(static::$domain, $_SERVER['REQUEST_URI']);
}

} //class

}