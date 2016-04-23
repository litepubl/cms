<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;
use litepubl\config;
use litepubl\debug\LoggerFactory;

class App
 {
    public  $cache;
    public  $classes;
public $controller;
public $context;
    public  $db;
    public  $logger;
    public  $memcache;
    public  $microtime;
    public  $options;
    public  $paths;
    public  $poolStorage;
    public  $router;
    public  $site;
    public  $storage;

    public  function __construct() {
         $this->microtime = microtime(true);
}

public function init() {
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
$this->cache = new CacheFile($this->paths->cache);
        } else if ( $this->memcache) {
             $this->storage = new StorageMemcache();
$this->cache = new CacheMemcache();
        } else {
             $this->storage = new Storage();
$this->cache = new CacheFile($this->paths->dir);
        }

         $this->poolStorage = new PoolStorage();
        if (! $this->poolStorage->isInstalled()) {
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

public function process() {
        if (Config::$debug) {
            error_reporting(-1);
            ini_set('display_errors', 1);
            Header('Cache-Control: no-cache, must-revalidate');
            Header('Pragma: no-cache');
        }

try {
$context = new Context(
new Request($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']),
new Response()
);

$this->context = $context;
$controller = new Controller();
$this->controller = $controller;

if ($controller->ob_cacheEnabled) {
            ob_start();
}

        if (Config::$beforeRequest && is_callable(Config::$beforeRequest)) {
            call_user_func_array(Config::$beforeRequest, [$this]);
        }

if (!$controller->cached($context) &&
$this->router->request($context)) {
$controller->request($context);
}

        // production mode: no debug and enabled buffer
if ($controller->ob_cacheEnabled) {
$this->flushLogg();
            while (@ob_end_flush());
            flush();
}

$this->router->close();
} catch (\Exception $e) {
$this->logException($e);
}
}

    public  function run() {
        try {
             $this->init();
            if (!config::$ignoreRequest) {
$this->process();
        }
        catch(\Exception $e) {
             $this->logException($e);
        }

$this->poolStorage->commit();
         $this->showErrors();
    }

public function getLogger() {
if (!$this->logger) {
$this->logger = LoggerFactory::create($this->paths);
}

return $this->logger;
}

public function log($level, $message, array $context = array()) {
//echo str_replace($this->paths->lib, '', $message);
//ignore debug messages
if (!config::$debug && ($level == 'debug') && (config::$logLevel != 'debug')) {
return;
}

$this->getLogger()->log($level, $message, $context);
}

public function logException(\Exception $e) {
$this->log('alert', LoggerFactory::getException($e));
}

    public function showErrors() {
        if (Config::$debug && $this->errorlog && ($this->options->echoexception || $this->options->admincookie || $this->router->adminpanel)) {
            echo $this->errorlog;
        }
    }

}