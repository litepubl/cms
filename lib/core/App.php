<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;
use litepubl\config;

class App
 {
    public  $cache;
    public  $classes;
    public  $datastorage;
    public  $db;
    public  $debug;
    public  $domain;
    public  $log;
    public  $memcache;
    public  $microtime;
    public  $options;
    public  $paths;
    public  $router;
    public  $secret;
    public  $site;
    public  $storage;
    public  $urlmap;

    public  function __construct() {
         $this->microtime = microtime(true);


        //functions in global namespace
        require_once (__DIR__ . '/utils.functions.php');

        //backward compability, in near future will be removed on config::$secret
         $this->secret = config::$secret;
         $this->debug = config::$debug || (defined('litepublisher_mode') && (litepublisher_mode == 'debug'));
         $this->domain =  $this->getHost();
         $this->createAliases();
         $this->createInstances();
    }

    public function createAliases() {
        class_alias(get_called_class() , 'litepublisher');
        class_alias(get_called_class() , 'litepubl\litepublisher');
        class_alias(get_called_class() , 'litepubl');
        class_alias(get_called_class() , 'litepubl\litepubl');
    }

    public  function createInstances() {
         $this->paths = new paths();
         $this->createStorage();
         $this->classes = Classes::i();
         $this->options = Options::i();
         $this->site = Site::i();
         $this->db = DB::i();
         $this->router = Router::i();
$this->urlmap = $this->router;
$this->router->cache = $this->cache;
    }

    public  function createStorage() {
        if (config::$memcache && class_exists('Memcache')) {
             $this->memcache = new \Memcache;
             $this->memcache->connect(isset(config::$memcache['host']) ? config::$memcache['host'] : '127.0.0.1', isset(config::$memcache['port']) ? config::$memcache['port'] : 1211);
        }

        if (isset(config::$classes['storage']) && class_exists(config::$classes['storage'])) {
            $classname = config::$classes['storage'];
             $this->storage = new $classname();
$this->cache = new CacheFile();
        } else if ( $this->memcache) {
             $this->storage = new StorageMemcache();
$this->cache = new CacheMemcache();
        } else {
             $this->storage = new Storage();
$this->cache = new CacheFile();
        }

         $this->datastorage = new DataStorage();
        if (! $this->datastorage->isInstalled()) {
            require ( $this->paths->lib . 'install/install.php');
            //exit() in lib/install/install.php
                    }
    }

    public  function cachefile($filename) {
        if (! $this->memcache) {
            return file_get_contents($filename);
        }

        if ($s =  $this->memcache->get($filename)) {
            return $s;
        }

        $s = file_get_contents($filename);
         $this->memcache->set($filename, $s, false, 3600);
        return $s;
    }

    public  function getHost() {
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

    public  function request() {
        if ( $this->debug) {
            error_reporting(-1);
            ini_set('display_errors', 1);
            Header('Cache-Control: no-cache, must-revalidate');
            Header('Pragma: no-cache');
        }

        if (config::$beforeRequest && is_callable(config::$beforeRequest)) {
            call_user_func_array(config::$beforeRequest, []);
        }

        return  $this->router->request( $this->domain, $_SERVER['REQUEST_URI']);
    }

    public  function run() {
        try {
             :init();

            if (!config::$ignoreRequest) {
                 ::request();
            }
        }
        catch(\Exception $e) {
             $this->options->handexception($e);
        }

         $this->options->savemodified();
         $this->options->showerrors();
    }

    public  function start() {
        if (\version_compare(\PHP_VERSION, '5.4', '<')) {
            die('Lite Publisher requires PHP 5.4 or later. You are using PHP ' . \PHP_VERSION);
        }

        if (isset(config::$classes['root']) && class_exists(config::$classes['root'])) {
            \call_user_func_array(config::$classes['root'], 'run', []);
        } else {
             ::run();
        }
    }

} //class

litepubl::start();