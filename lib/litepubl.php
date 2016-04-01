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
  public static $datastorage;
  public static $db;
  public static $debug ;
  public static $domain;
  public static $log;
  public static $memcache;
  public static $microtime;
  public static $options;
  public static $paths;
  public static $secret;
  public static $site;
  public static $storage;
  public static $urlmap;

  public static function init() {
    static::$microtime = microtime(true);
//backward compability, in near future will be removed on config::$secret
static::$secret = config::$secret;
static::$debug = config::$debug || (defined('litepublisher_mode') && (litepublisher_mode == 'debug'));
    static::$domain = static::getHost();
static::createAliases();
static::createInstances();
  }

public function createAliases() {
\class_alias(get_called_class(), 'litepublisher');
\class_alias(get_called_class(), 'litepubl');
\class_alias('tdata', 'litepubl\tdata');
}

public static function createInstances() {
    static::$paths = new tpaths();
static::createStorage();
  static::$classes = \tclasses::i();
  static::$options = \toptions::i();
  static::$site = \tsite::i();
  static::$db = \tdatabase::i();
//static::$cache = new cache();
  static::$urlmap = \turlmap::i();
}

public static function createStorage() {
if (config::$memcache && class_exists('Memcache')) {
    static::$memcache =  new Memcache;
    static::$memcache->connect(
isset(config::$memcache['host']) ? config::$memcache['host'] : '127.0.0.1',
isset(config::$memcache['post']) ? config::$memcache['post'] :  1211);
}

if (isset(config::$classes['storage']) && class_exists(config::$classes['storage'])) {
$classname = config::$classes['storage'];
  static::$storage = new $classname();
} else if (static::$memcache) {
  static::$storage = new memcachestorage();
} else {
  static::$storage = new storage();
}

static::$datastorage = new datastorage();
static::$datastorage->loaddata();
if (!static::$datastorage->isInstalled()) {
    require(static::$paths->lib . 'install/install.php');
//exit() in lib/install/install.php
}
}

public static function cachefile($filename) {
if (!static::$memcache) {
return file_get_contents($filename);
}

if ($s = static::$memcache->get($filename)) {
return $s;
}

$s = file_get_contents($filename);
static::$memcache->set($filename, $s, false, 3600);
return $s;
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
    \error_reporting(-1);
    \ini_set('display_errors', 1);
 \Header( 'Cache-Control: no-cache, must-revalidate');
  \Header( 'Pragma: no-cache');
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