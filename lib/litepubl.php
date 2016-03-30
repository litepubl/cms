<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class litepubl {
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
    if (!preg_match('/(www\.)?([\w\.\-]+)(:\d*)?/', strtolower(trim($_SERVER['HTTP_HOST'])) , $domain)) {
      die('cant resolve domain name');
    }

    self::$domain = $domain[2];

    self::$paths = new tpaths();
    self::$microtime = microtime(true);
  }

} //class

