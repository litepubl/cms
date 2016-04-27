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
use litepubl\debug\LogManager;

class App
 {
    public  $cache;
    public  $classes;
public $controller;
public $context;
    public  $db;
    public  $logManager;
    public  $memcache;
    public  $microtime;
public $onClose;
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
//check before create any instances
$installed = $this->poolStorage->isInstalled();
$this->createCache();
         $this->classes = Classes::i();
         $this->options = Options::i();
         $this->site = Site::i();
         $this->router = Router::i();
$this->onClose = new Callback();

        if ($installed) {
         $this->db = DB::i();
} else {
            require ( $this->paths->lib . 'install/install.php');
            //exit() in lib/install/install.php
                    }
    }

    public  function createStorage() {
        if (config::$memcache && class_exists('Memcache')) {
             $this->memcache = new \Memcache;
             $this->memcache->connect(isset(config::$memcache['host']) ? config::$memcache['host'] : '127.0.0.1', isset(config::$memcache['port']) ? config::$memcache['port'] : 1211);
        }

        if (isset(config::$classes['storage']) && class_exists(config::$classes['storage'])) {
            $classname = config::$classes['storage'];
             $this->storage = new $classname();
        } else if ( $this->memcache) {
             $this->storage = new StorageMemcache();
        } else {
             $this->storage = new Storage();
        }

         $this->poolStorage = new PoolStorage();
    }

public function createCache()
{
if ($this->memcache) {
$this->cache = new CacheMemcache($this->memcache, $this->options->expiredcache, $this->paths->home);
} else {
$this->cache = new CacheFile($this->paths->cache, $this->options->expiredcache, $this->options->filetime_offset);
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

        $obEnabled = !Config::$debug &&  $this->options->ob_cache;
if ($obEnabled) {
            ob_start();
}

        if (Config::$beforeRequest && is_callable(Config::$beforeRequest)) {
            call_user_func_array(Config::$beforeRequest, [$this]);
        }

if (!$controller->cached($context)) {
$this->router->request($context);
$controller->request($context);
$this->router->afterrequest($context);
}

$this->showErrors();

if ($obEnabled) {	
if ($this->onClose->getCount()) {
        ignore_user_abort(true);
$context->response->closeConnection();
            while (@ob_end_flush());
            flush();

                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }

//prevent any output
ob_start();
} else {
            while (@ob_end_flush());
}
}

$this->onclose->fire();
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
        } catch(\Exception $e) {
             $this->logException($e);
        }

$this->poolStorage->commit();
$this->showErrors();
    }

public function getLogManager()
 {
if (!$this->logManager) {
if (isset(Config::$classes['logmanager'])) {
$class = Config::$classes['logmanager'];
$this->logManager = new $class($this);
} else {
$this->logManager = new LogManager($this);
}
}

return $this->logManager;
}

public function getLogger() 
{
return $this->getLogManager()->logger;
}

public function logException(\Exception $e) {
$this->getLogManager()->logException($e);
}

    public function showErrors() {
        if (Config::$debug && $this->logManager && ($this->options->echoexception || $this->options->admincookie)) {
            echo $this->logManager->getHtml();
        }
    }

public function redirExit($url)
{
$this->poolStorage->commit();
if (!Str::begin($url, 'http')) {
$url = $this->site->url . $url;
}

header('HTTP/1.1 307 Temporary Redirect', true, 307);
header('Location: '. $url);
exit();
}

}