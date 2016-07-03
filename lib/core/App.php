<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\core;

use litepubl\config;
use litepubl\debug\LogManager;

class App
{
    public $cache;
    public $classes;
    public $controller;
    public $context;
    public $db;
    public $installed;
    public $logManager;
    public $memcache;
    public $microtime;
    public $onClose;
    public $options;
    public $paths;
    public $poolStorage;
    public $router;
    public $site;
    public $storage;

    public function __construct()
    {
        $this->microtime = microtime(true);
    }

    public function init()
    {
        $this->createAliases();
        $this->createInstances();
    }

    public function createAliases()
    {
        $litepubl = __NAMESPACE__ . '\litepubl';
        class_alias($litepubl, 'litepublisher');
        class_alias($litepubl, 'litepubl\litepublisher');
        class_alias($litepubl, 'litepubl');
        class_alias($litepubl, 'litepubl\litepubl');
    }

    public function createInstances()
    {
        $this->paths = new paths();
        $this->createStorage();
        //check before create any instances
        $this->installed = $this->poolStorage->isInstalled();
        $this->classes = Classes::i();
        $this->options = Options::i();
        $this->site = Site::i();
        $this->router = Router::i();
        $this->controller = new Controller();
        $this->createCache();
        $this->onClose = new Callback();

        if ($this->installed) {
            $this->db = DB::i();
        } else {
            include $this->paths->lib . 'install/install.php';
            //exit() in lib/install/install.php
        }
    }

    public function createStorage()
    {
        if (config::$memcache && class_exists('Memcache')) {
            $this->memcache = new \Memcache;
            $this->memcache->connect(isset(config::$memcache['host']) ? config::$memcache['host'] : '127.0.0.1', isset(config::$memcache['port']) ? config::$memcache['port'] : 1211);
        }

        if (isset(config::$classes['storage']) && class_exists(config::$classes['storage'])) {
            $classname = config::$classes['storage'];
            $this->storage = new $classname();
        } elseif ($this->memcache) {
            $this->storage = new StorageMemcache();
        } else {
            $this->storage = new StorageInc();
        }

        $this->poolStorage = new PoolStorage();
    }

    public function createCache()
    {
        if ($this->memcache) {
            $this->cache = new CacheMemcache($this->memcache, $this->installed ? $this->options->expiredcache : 3600, $this->paths->home);
        } else {
            $this->cache = new CacheFile($this->paths->cache, $this->installed ? $this->options->expiredcache : 3600, $this->installed ? $this->options->filetime_offset : 0);
        }
    }

    public function cachefile(string $filename)
    {
        if (!$this->memcache) {
            return file_get_contents($filename);
        }

        if ($s = $this->memcache->get($filename)) {
            return $s;
        }

        $s = file_get_contents($filename);
        $this->memcache->set($filename, $s, false, 3600);
        return $s;
    }

    public function process()
    {
        if (Config::$debug) {
            error_reporting(-1);
            ini_set('display_errors', 1);
            Header('Cache-Control: no-cache, must-revalidate');
            Header('Pragma: no-cache');
        }

        try {
            $context = new Context(new Request($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']), new Response());

            $this->context = $context;

            $obEnabled = !Config::$debug && $this->options->ob_cache;
            if ($obEnabled) {
                ob_start();
            }

            if (Config::$beforeRequest && is_callable(Config::$beforeRequest)) {
                call_user_func_array(Config::$beforeRequest, [$this]);
            }

            if (!$this->controller->cached($context)) {
                $this->router->request($context);
                $this->controller->request($context);
                $this->router->afterrequest($context);
            }

            $this->showErrors();

            if ($obEnabled) {
                if ($this->onClose->getCount()) {
                    ignore_user_abort(true);
                    $context->response->closeConnection();
                    while (@ob_end_flush()) {
                    }
                    flush();

                    if (function_exists('fastcgi_finish_request')) {
                        fastcgi_finish_request();
                    }

                    //prevent any output
                    ob_start();
                } else {
                    while (@ob_end_flush()) {
                    }
                }
            }

            $this->onClose->fire();
        } catch (\Throwable $e) {
            $this->logException($e);
        }
    }

    public function run()
    {
        try {
            $this->init();
            if (!config::$ignoreRequest) {
                $this->process();
            }
        } catch (\Throwable  $e) {
            $this->logException($e);
        }

        $this->poolStorage->commit();
        $this->showErrors();
    }

    public function getLogManager(): LogManager
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

    public function logException(\Throwable $e)
    {
        $this->getLogManager()->logException($e);
    }

    public function showErrors()
    {
        if ($this->logManager && (Config::$debug || $this->options->echoexception || $this->options->adminFlag) && ($log = $this->logManager->getHtml())) {
            echo $log;
        }
    }

    public function redirExit(string $url)
    {
        $this->poolStorage->commit();
        if (!Str::begin($url, 'http')) {
            $url = $this->site->url . $url;
        }

        header('HTTP/1.1 307 Temporary Redirect', true, 307);
        header('Location: ' . $url);
        exit();
    }
}
