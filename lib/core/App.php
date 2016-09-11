<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\core;

use litepubl\config;
use litepubl\debug\LogManager;

class App
{
    use Callbacks;

    public $cache;
    public $classes;
    public $controller;
    public $context;
    public $db;
    public $installed;
    public $logManager;
    public $memcache;
    public $microtime;
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

        if ($this->installed) {
            try {
                        $this->db = DB::i();
            } catch (DBException $e) {
                        Config::$ignoreRequest = true;
                        $this->logException($e);
            }
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
            $context = new Context(new Request($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']), new Response());
$this->request($context);
}

    public function request(Context $context)
{
            $this->context = $context;
        if (Config::$debug) {
            error_reporting(-1);
            ini_set('display_errors', 1);
            Header('Cache-Control: no-cache, must-revalidate');
            Header('Pragma: no-cache');
        }

try {
            $obEnabled = !Config::$debug && $this->options->ob_cache;
            if ($obEnabled) {
                ob_start();
            }

            if (is_callable(Config::$beforeRequest)) {
                call_user_func_array(Config::$beforeRequest, [$this]);
            }

            if ($this->controller->cached($context)) {
                                $this->controller->request($context);
                } else {
                $this->router->request($context);

                if ($context->response->isRedir()) {
                                $context->response->send();
                }

                $this->router->afterRequest(['context' => $context]);
            }

            $this->showErrors();

            if ($obEnabled) {
                if ($this->getCallbacksCount('onclose')) {
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

            $this->triggerCallback('onclose');
        } catch (\Throwable $e) {
            $this->logException($e);
        }
    }

    public function run()
    {
        try {
            $this->init();
            if (is_callable(config::$afterInit)) {
                call_user_func_array(Config::$afterInit, [$this]);
            }

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

    public function log(string $mesg, array $context = [])
    {
        if (Config::$debug) {
            $this->getLogger()->debug($mesg, $context);
        }
    }

    public function logException(\Throwable $e)
    {
$itemRoute = isset($this->context) ? $this->context->itemRoute : [];
        $this->getLogManager()->logException($e, $itemRoute);
    }

    public function showErrors()
    {
$r = ['show' => $this->logManager && (Config::$debug || $this->options->echoexception || $this->options->adminFlag)];
            $r = $this->triggerCallback('onShowErrors', $r);
        if ($r['show'] && ($log = $this->logManager->getHtml())) {
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

    /**
     * To compability with Callbacks trait
     */

    public function getApp(): App
    {
        return $this;
    }

    public function onClose(callable $callback)
    {
        $this->addCallback('onclose', $callback);
    }
}
