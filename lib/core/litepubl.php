<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;
use litepubl\config;

class litepubl
 {
    public static $cache;
    public static $classes;
    public static $datastorage;
    public static $db;
    public static $debug;
    public static $domain;
    public static $log;
    public static $memcache;
    public static $microtime;
    public static $options;
    public static $paths;
    public static $router;
    public static $secret;
    public static $site;
    public static $storage;
    public static $urlmap;

    public static function init() {
        static ::$microtime = microtime(true);

        //functions in global namespace
        require_once (__DIR__ . '/utils.functions.php');

        //backward compability, in near future will be removed on config::$secret
        static ::$secret = config::$secret;
        static ::$debug = config::$debug || (defined('litepublisher_mode') && (litepublisher_mode == 'debug'));
        static ::$domain = static ::getHost();
        static ::createAliases();
        static ::createInstances();
    }

    public function createAliases() {
        \class_alias(get_called_class() , 'litepublisher');
        \class_alias(get_called_class() , 'litepubl\litepublisher');
        \class_alias(get_called_class() , 'litepubl');
        \class_alias(get_called_class() , 'litepubl\litepubl');
    }

    public static function createInstances() {
        static ::$paths = new paths();
        static ::createStorage();
        static ::$classes = Classes::i();
        static ::$options = Options::i();
        static ::$site = Site::i();
        static ::$db = DB::i();
        static ::$router = Router::i();
static::$urlmap = static::$router;
static::$router->cache = static::$cache;
    }

    public static function createStorage() {
        if (config::$memcache && class_exists('Memcache')) {
            static ::$memcache = new Memcache;
            static ::$memcache->connect(isset(config::$memcache['host']) ? config::$memcache['host'] : '127.0.0.1', isset(config::$memcache['port']) ? config::$memcache['port'] : 1211);
        }

        if (isset(config::$classes['storage']) && class_exists(config::$classes['storage'])) {
            $classname = config::$classes['storage'];
            static ::$storage = new $classname();
static::$cache = new CacheFile();
        } else if (static ::$memcache) {
            static ::$storage = new StorageMemcache();
static::$cache = new CacheMemcache();
        } else {
            static ::$storage = new Storage();
static::$cache = new CacheFile();
        }

        static ::$datastorage = new DataStorage();
        static ::$datastorage->loaddata();
        if (!static ::$datastorage->isInstalled()) {
            require (static ::$paths->lib . 'install/install.php');
            //exit() in lib/install/install.php
                    }
    }

    public static function cachefile($filename) {
        if (!static ::$memcache) {
            return file_get_contents($filename);
        }

        if ($s = static ::$memcache->get($filename)) {
            return $s;
        }

        $s = file_get_contents($filename);
        static ::$memcache->set($filename, $s, false, 3600);
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

        if (config::$dieOnInvalidHost) {
            die('cant resolve domain name');
        }
    }

    public static function request() {
        if (static ::$debug) {
            \error_reporting(-1);
            \ini_set('display_errors', 1);
            \Header('Cache-Control: no-cache, must-revalidate');
            \Header('Pragma: no-cache');
        }

        if (config::$beforeRequest && \is_callable(config::$beforeRequest)) {
            \call_user_func_array(config::$beforeRequest, []);
        }

        return static ::$urlmap->request(static ::$domain, $_SERVER['REQUEST_URI']);
    }

    public static function run() {
        try {
            static ::init();

            if (!config::$ignoreRequest) {
                static ::request();
            }
        }
        catch(\Exception $e) {
            static ::$options->handexception($e);
        }

        static ::$options->savemodified();
        static ::$options->showerrors();
    }

    public static function start() {
        if (\version_compare(\PHP_VERSION, '5.4', '<')) {
            die('Lite Publisher requires PHP 5.4 or later. You are using PHP ' . \PHP_VERSION);
        }

        if (isset(config::$classes['root']) && class_exists(config::$classes['root'])) {
            \call_user_func_array(config::$classes['root'], 'run', []);
        } else {
            static ::run();
        }
    }

} //class

litepubl::start();