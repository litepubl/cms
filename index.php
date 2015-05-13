<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

if (version_compare(PHP_VERSION, '5.1', '<')) {
  die('Lite Publisher requires PHP 5.1 or later. You are using PHP ' . PHP_VERSION) ;
}

class litepublisher {
  public static $db;
  public static $storage;
  public static $classes;
  public static $options;
  public static $site;
  public static $urlmap;
  public static $paths;
  public static $domain;
  public static $debug = false;
  public static $secret = '8r7j7hbt8iik//pt7hUy5/e/7FQvVBoh7/Zt8sCg8+ibVBUt7rQ';
  public static $microtime;
  
  public static function init() {
    if (defined('litepublisher_mode') && (litepublisher_mode == 'debug')) litepublisher::$debug = true;
    if (!preg_match('/(www\.)?([\w\.\-]+)(:\d*)?/', strtolower(trim($_SERVER['HTTP_HOST'])) , $domain)) die('cant resolve domain name');
    self::$domain = $domain[2];
    
    $home = dirname(__file__) . DIRECTORY_SEPARATOR;
    $storage = $home . 'storage' . DIRECTORY_SEPARATOR;
    
    $paths = new tpaths();
    self::$paths = $paths;
    $paths->home = $home;
    $paths->lib = $home .'lib'. DIRECTORY_SEPARATOR;
    $paths->data = $storage . 'data'. DIRECTORY_SEPARATOR;
    $paths->cache = $storage . 'cache'. DIRECTORY_SEPARATOR;
    $paths->libinclude = $home .'lib'. DIRECTORY_SEPARATOR . 'include'. DIRECTORY_SEPARATOR;
    $paths->languages = $home .'lib'. DIRECTORY_SEPARATOR . 'languages'. DIRECTORY_SEPARATOR;
    $paths->storage = $storage;
    $paths->backup = $storage . 'backup' . DIRECTORY_SEPARATOR;
    $paths->plugins =  $home . 'plugins' . DIRECTORY_SEPARATOR;
    $paths->themes = $home . 'themes'. DIRECTORY_SEPARATOR;
    $paths->files = $home . 'files' . DIRECTORY_SEPARATOR;
    $paths->js = $home . 'js' . DIRECTORY_SEPARATOR;
    self::$microtime = microtime(true);
  }
  
}//class

class tpaths {
  public $home;
  public $lib;
  public $data;
  public $cache;
  public $backup;
  public $storage;
  public $libinclude;
  public $js;
  public $plugins;
  public $themes;
  public $files;
}

try {
  litepublisher::init();
  if (litepublisher::$debug) {
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);
    require_once(litepublisher::$paths->lib . 'data.class.php');
    require_once(litepublisher::$paths->lib . 'events.class.php');
    require_once(litepublisher::$paths->lib . 'items.class.php');
    require_once(litepublisher::$paths->lib . 'classes.class.php');
    require_once(litepublisher::$paths->lib . 'options.class.php');
    require_once(litepublisher::$paths->lib . 'site.class.php');
  } else {
    require_once(litepublisher::$paths->lib . 'kernel.php');
  }
  
  define('dbversion', true);
  /*
  if (class_exists('Memcache')) {
    tfilestorage::$memcache =  new Memcache;
    tfilestorage::$memcache->connect('127.0.0.1', 11211);
  }
  */
  
  if (!tstorage::loaddata()) {
    if (file_exists(litepublisher::$paths->data . 'storage.php') && filesize(litepublisher::$paths->data . 'storage.php')) die('Storage not loaded');
    require_once(litepublisher::$paths->lib .'install' . DIRECTORY_SEPARATOR . 'install.php');
  }
  
  litepublisher::$classes = tclasses::i();
  litepublisher::$options = toptions::i();
  litepublisher::$db = tdatabase::i();
  litepublisher::$site = tsite::i();
  litepublisher::$urlmap = turlmap::i();
  
  if (!defined('litepublisher_mode')) {
    litepublisher::$urlmap->request(strtolower($_SERVER['HTTP_HOST']), $_SERVER['REQUEST_URI']);
  }
} catch (Exception $e) {
  litepublisher::$options->handexception($e);
}
litepublisher::$options->savemodified();
litepublisher::$options->showerrors();