<?php
//App.php
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
            require ($this->paths->lib . 'install/install.php');
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
        } else if ($this->memcache) {
            $this->storage = new StorageMemcache();
        } else {
            $this->storage = new Storage();
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

    public function cachefile($filename)
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
            $context = new Context(new Request($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']) , new Response());

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

            $this->onClose->fire();
        }
        catch(\Exception $e) {
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
        }
        catch(\Exception $e) {
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

    public function logException(\Exception $e)
    {
        $this->getLogManager()->logException($e);
    }

    public function showErrors()
    {
        if ($this->logManager && (Config::$debug || $this->options->echoexception || $this->options->adminFlag) && ($log = $this->logManager->getHtml())) {
            echo $log;
        }
    }

    public function redirExit($url)
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

//AppTrait.php
namespace litepubl\core;

trait AppTrait
{

    public static function getAppInstance()
    {
        return litepubl::$app;
    }

    public function getApp()
    {
        return static ::getAppInstance();
    }

}

//Arr.php
namespace litepubl\core;

class Arr
{
    public static function delete(array & $a, $i)
    {
        array_splice($a, $i, 1);
    }

    public static function deleteValue(array & $a, $value)
    {
        $i = array_search($value, $a);
        if ($i !== false) {
            array_splice($a, $i, 1);
            return true;
        }

        return false;
    }

    public static function clean(array & $items)
    {
        $items = array_unique($items);
        foreach (array(
            0,
            false,
            null,
            ''
        ) as $v) {
            $i = array_search($v, $items);
            if (($i !== false) && ($items[$i] === $v)) {
                array_splice($items, $i, 1);
            }
        }
    }

    public static function insert(array & $a, $item, $index)
    {
        array_splice($a, $index, 0, array(
            $item
        ));
    }

    public static function move(array & $a, $oldindex, $newindex)
    {
        //delete and insert
        if (($oldindex == $newindex) || !isset($a[$oldindex])) {
            return false;
        }

        $item = $a[$oldindex];
        array_splice($a, $oldindex, 1);
        array_splice($a, $newindex, 0, array(
            $item
        ));
    }

    public static function toEnum($v, array $a)
    {
        $v = trim($v);
        return in_array($v, $a) ? $v : $a[0];
    }

    public static function reIndex(array & $a)
    {
        array_splice($a, count($a) , 0, array());
    }

    public static function append(array & $a, $index, $value)
    {
        while (array_key_exists($index, $a)) {
            $index++;
        }

        $a[$index] = $value;
    }

}

//BaseCache.php
namespace litepubl\core;

class BaseCache
{
    protected $items = [];
    protected $lifetime = 3600;

    public function getString($filename)
    {
    }

    public function setString($filename, $str)
    {
    }

    public function set($filename, $data)
    {
        $this->setString($filename, $this->serialize($data));
    }

    public function get($filename)
    {
        if ($s = $this->getString($filename)) {
            return $this->unserialize($s);
        }

        return false;
    }

    public function serialize($data)
    {
        return serialize($data);
    }

    public function unserialize(&$data)
    {
        return unserialize($data);
    }

    public function savePhp($filename, $str)
    {
        $this->setString($filename, $str);
    }

    public function includePhp($filename)
    {
        if ($str = $this->getString($filename)) {
            eval('?>' . $str);
            return true;
        }

        return false;
    }

    public function exists($filename)
    {
        return array_key_exists($this->items);
    }

    public function setLifetime($value)
    {
        $this->lifetime = $value;
    }

    public function clearUrl($url)
    {
        $this->delete(md5($url) . '.php');
    }

}

//CacheFile.php
namespace litepubl\core;

class CacheFile extends BaseCache
{
    protected $dir;
    protected $timeOffset;

    public function __construct($dir, $lifetime, $timeOffset)
    {
        $this->dir = $dir;
        $this->timeOffset = $timeOffset;
        $this->lifetime = $lifetime - $timeOffset;
        $this->items = [];
    }

    public function getDir()
    {
        return $this->dir;
    }

    public function setString($filename, $str)
    {
        $this->items[$filename] = $str;
        $fn = $this->getdir() . $filename;
        file_put_contents($fn, $str);
        @chmod($fn, 0666);
    }

    public function getString($filename)
    {
        if (array_key_exists($filename, $this->items)) {
            return $this->items[$filename];
        }

        $fn = $this->getdir() . $filename;
        if (file_exists($fn) && (filemtime($fn) + $this->lifetime >= time())) {
            return $this->items[$filename] = file_get_contents($fn);
        }

        return false;
    }

    public function delete($filename)
    {
        unset($this->items[$filename]);
        $fn = $this->getdir() . $filename;
        if (file_exists($fn)) {
            unlink($fn);
        }
    }

    public function exists($filename)
    {
        return array_key_exists($filename, $this->items) || (file_exists($this->getdir() . $filename) && (filemtime($this->getDir() . $filename) + $this->lifetime >= time()));
    }

    public function setLifetime($value)
    {
        $this->lifetime = $value - $this->timeOffset;
    }

    public function clear()
    {
        $this->items = [];
        $this->clearDir($path = $this->getdir());
    }

    public function clearDir($dir)
    {
        if ($h = @opendir($path)) {
            while (FALSE !== ($filename = @readdir($h))) {
                if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) {
                    continue;
                }

                $file = $path . $filename;
                if (is_dir($file)) {
                    $this->clearDir($file . DIRECTORY_SEPARATOR);
                    unlink($file);
                } else {
                    unlink($file);
                }
            }
            closedir($h);
        }
    }

    public function includePhp($filename)
    {
        $fn = $this->getDir() . $filename;
        if (file_exists($fn) && (filemtime($fn) + $this->lifetime >= time())) {
            if (defined('HHVM_VERSION')) {
                // XXX: workaround for https://github.com/facebook/hhvm/issues/1447
                eval('?>' . file_get_contents($fn));
            } else {
                include $fn;
            }
            return true;
        }

        return false;
    }

}

//CacheMemcache.php
namespace litepubl\core;

class CacheMemcache extends BaseCache
{
    protected $memcache;
    protected $prefix;
    protected $revision;
    protected $revisionKey;

    public function __construct($memcache, $lifetime, $prefix)
    {
        $this->memcache = $memcache;
        $this->lifetime = $lifetime;
        $this->prefix = $prefix . ':cache:';
        $this->revision = 0;
        $this->revisionKey = 'cache_revision';
        $this->items = [];
        $this->getRevision();
    }

    public function getPrefix()
    {
        return $this->prefix . $this->revision . '.';
    }

    public function getRevision()
    {
        return $this->revision = (int)$this->memcache->get($this->prefix . $this->revisionKey);
    }

    public function clear()
    {
        $this->revision++;
        $this->memcache->set($this->prefix . $this->revisionKey, "$this->revision", false, $this->lifetime);
        $this->items = [];
    }

    public function setString($filename, $str)
    {
        $this->items[$filename] = $str;
        $this->memcache->set($this->getPrefix() . $filename, $str, false, $this->lifetime);
    }

    public function getString($filename)
    {
        if (array_key_exists($filename, $this->items)) {
            return $this->items[$filename];
        }

        return $this->memcache->get($this->getPrefix() . $filename);
    }

    public function delete($filename)
    {
        unset($this->items[$filename]);
        $this->memcache->delete($this->getPrefix() . $filename);
    }

    public function exists($filename)
    {
        if (parent::exists($filename)) {
            return $this->items[$filename] !== false;
        }

        return $this->items[$filename] = $this->getString($filename);
    }

}

//CacheStorageTrait.php
namespace litepubl\core;

trait CacheStorageTrait
{

    public function getStorage()
    {
        return $this->getApp()->cache;
    }

}

//Callback.php
namespace litepubl\core;

class Callback
{
    private $events;

    public function __construct()
    {
        $this->events = [];
    }

    public function on()
    {
        return $this->add(func_get_args());
    }

    public function add(array $callback)
    {
        if (count($callback)) {
            $this->events[] = $callback;
            $indexes = array_keys($this->events);
            return $indexes[count($indexes) - 1];
        }
    }

    public function delete($index)
    {
        if (isset($this->events[$index])) {
            unset($this->events[$index]);
        }
    }

    public function clear()
    {
        $this->events = [];
    }

    public function getCount()
    {
        return count($this->events);
    }

    public function fire()
    {
        foreach ($this->events as $a) {
            try {
                $c = array_shift($a);
                if (!is_callable($c)) {
                    $c = [$c, array_shift($a) ];
                }

                call_user_func_array($c, $a);
            }
            catch(\Exception $e) {
                litepubl::$app->logException($e);
            }
        }
    }

}

//CancelEvent.php
namespace litepubl\core;

class CancelEvent extends \Exception
{
    public $result;

    public function __construct($message, $code = 0)
    {
        $this->result = $message;
        parent::__construct('', 0);
    }
}

//Classes.php
namespace litepubl\core;

use litepubl\Config;

class Classes extends Items
{
    use PoolStorageTrait;

    public $namespaces;
    public $kernel;
    public $remap;
    public $classmap;
    public $aliases;
    public $instances;
    public $loaded;
    private $composerLoaded;

    public static function i()
    {
        $app = static ::getAppInstance();
        if (!isset($app->classes)) {
            $classname = get_called_class();
            $app->classes = new $classname();
            $app->classes->instances[$classname] = $app->classes;
        }

        return $app->classes;
    }

    protected function create()
    {
        parent::create();
        $this->basename = 'classes';
        $this->dbversion = false;
        $this->addevents('onnewitem', 'onrename');
        $this->addmap('namespaces', ['litepubl' => 'lib']);
        $this->addmap('kernel', array());
        $this->addmap('remap', array());
        $this->instances = array();
        $this->classmap = [];
        $this->aliases = [];
        $this->loaded = [];
        $this->composerLoaded = false;

        spl_autoload_register(array(
            $this,
            'autoload'
        ) , true, true);
    }

    public function getInstance($class)
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        if (isset($this->aliases[$class]) && ($alias = $this->aliases[$class]) && ($alias != $class)) {
            return $this->getinstance($alias);
        }

        if (!class_exists($class)) {
            $this->error(sprintf('Class "%s" not found', $class));
        }

        return $this->instances[$class] = $this->newinstance($class);
    }

    public function newinstance($class)
    {
        if (!empty($this->remap[$class])) {
            $class = $this->remap[$class];
        }

        return new $class();
    }

    public function newItem($name, $class, $id)
    {
        if (!empty($this->remap[$class])) {
            $class = $this->remap[$class];
        }

        $this->callevent('onnewitem', array(
            $name, &$class,
            $id
        ));

        return new $class();
    }

    public function add($class, $filename, $deprecatedPath = false)
    {
        if ($incfilename = $this->findPSR4($class)) {
            $this->include($incfilename);
        } else {
            if (isset($this->items[$class]) && ($this->items[$class] == $filename)) {
                return false;
            }

            $this->lock();
            if (!class_exists($class, false) && !strpos($class, '\\')) {
                $class = 'litepubl\\' . $class;
                $filename = sprintf('plugins/%s%s', $deprecatedPath ? $deprecatedPath . '/' : '', $filename);
            }

            $this->items[$class] = $filename;
        }

        $this->installClass($class);
        $this->unlock();
        $this->added($class);
        return true;
    }

    public function installClass($classname)
    {
        $instance = $this->getinstance($classname);
        if (method_exists($instance, 'install')) {
            $instance->install();
        }

        return $instance;
    }

    public function uninstallClass($classname)
    {
        if (class_exists($classname)) {
            $instance = $this->getinstance($classname);
            if (method_exists($instance, 'uninstall')) {
                $instance->uninstall();
            }
        }
    }

    public function delete($class)
    {
        if (!isset($this->items[$class])) {
            return false;
        }

        $this->lock();
        $this->uninstallClass($class);
        unset($this->items[$class]);
        unset($this->kernel[$class]);
        $this->unlock();
        $this->deleted($class);
    }

    public function reinstall($class)
    {
        if (isset($this->items[$class])) {
            $this->lock();
            $filename = $this->items[$class];
            $kernel = isset($this->kernel[$class]) ? $this->kernel[$class] : false;
            $this->delete($class);
            $this->add($class, $filename);
            if ($kernel) {
                $this->kernel[$class] = $kernel;
            }
            $this->unlock();
        }
    }

    public function baseclass($classname)
    {
        if ($i = strrpos($classname, '\\')) {
            return substr($classname, $i + 1);
        }

        return $classname;
    }

    public function addAlias($classname, $alias)
    {
        if (!$alias) {
            if ($i = strrpos($classname, '\\')) {
                $alias = substr($classname, $i + 1);
            } else {
                $alias = 'litepubl\\' . $classname;
            }
        }

        //may be exchange class names
        if (class_exists($alias, false) && !class_exists($classname, false)) {
            $tmp = $classname;
            $classname = $alias;
            $alias = $tmp;
        }

        if (!isset($this->aliases[$alias])) {
            class_alias($classname, $alias, false);
            $this->aliases[$alias] = $classname;
        }
    }

    public function getClassmap($classname)
    {
        if (isset($this->aliases[$classname])) {
            return $this->aliases[$classname];
        }

        if (!count($this->classmap)) {
            $this->classmap = include ($this->getApp()->paths->lib . 'update/classmap.php');
        }
        $classname = $this->baseclass($classname);
        if (isset($this->classmap[$classname])) {
            $result = $this->classmap[$classname];
            if (!isset($this->aliases[$classname])) {
                class_alias($result, $classname, false);
                $this->aliases[$classname] = $result;
            }

            $classname = 'litepubl\\' . $classname;
            if (!isset($this->aliases[$classname])) {
                class_alias($result, $classname, false);
                $this->aliases[$classname] = $result;
            }

            return $result;
        }

        return false;
    }

    public function autoload($classname)
    {
        if (isset($this->loaded[$classname])) {
            return;
        }

        if (config::$useKernel && !config::$debug && ($filename = $this->findKernel($classname))) {
            include_once $filename;
            if (class_exists($classname, false) || interface_exists($classname, false) || trait_exists($classname, false)) {
                $this->loaded[$classname] = $filename;
                return;
            }
        }

        $filename = $this->findFile($classname);
        $this->loaded[$classname] = $filename;
        if ($filename) {
            include_once $filename;
        } elseif (!$this->composerLoaded) {
            $this->composerLoaded = true;
            $this->loadComposer($classname);
        }
    }

    public function findFile($classname)
    {
        /*
        if ($newclass = $this->getClassmap($classname)) {
        $classname = $newclass;
        }
        */

        $result = $this->findPSR4($classname);
        if (!$result) {
            $result = $this->findClassmap($classname);
        }

        return $result;
    }

    public function findClassmap($classname)
    {
        if (isset($this->items[$classname])) {
            $filename = $this->app->paths->home . $this->items[$classname];
            if (file_exists($filename)) {
                return $filename;
            }
        }
    }

    public function include ($filename)
    {
        //if (is_dir($filename)) $this->error($filename);
        require_once $filename;
    }

    public function include_file($filename)
    {
        if ($filename && file_exists($filename)) {
            $this->include($filename);
        }
    }

    public function findPSR4($classname)
    {
        if (false === ($i = strrpos($classname, '\\'))) {
            return false;
        }

        $ns = substr($classname, 0, $i);
        if ($ns[0] == '\\') {
            $ns = substr($ns, 1);
        }

        $baseclass = substr($classname, $i + 1);
        $paths = $this->app->paths;

        if (isset($this->loaded[$ns])) {
            $filename = $this->loaded[$ns] . $baseclass . '.php';
            if (file_exists($filename)) {
                return $filename;
            }

            return false;
        }

        if (isset($this->namespaces[$ns])) {
            $dir = $paths->home . $this->namespaces[$ns] . '/';
            $filename = $dir . $baseclass . '.php';
            if (file_exists($filename)) {
                $this->loaded[$ns] = $dir;
                return $filename;
            }
        }

        $names = explode('\\', $ns);
        $vendor = array_shift($names);
        while (count($names)) {
            if (isset($this->namespaces[$vendor])) {
                $dir = $paths->home . $this->namespaces[$vendor] . '/' . implode('/', $names) . '/';
                $filename = $dir . $baseclass . '.php';
                if (file_exists($filename)) {
                    $this->loaded[$ns] = $dir;
                    return $filename;
                }
            }

            $vendor.= '\\' . array_shift($names);
        }

        //last chanse
        $name = 'litepubl\plugins';
        if (Str::begin($ns, $name)) {
            $dir = $paths->plugins . $this->subSpace($ns, $name) . '/';
            $filename = $dir . $baseclass . '.php';
            if (file_exists($filename)) {
                $this->loaded[$ns] = $dir;
                return $filename;
            }
        }

        return false;
    }

    public function findKernel($classname)
    {
        if (false === ($i = strrpos($classname, '\\'))) {
            return false;
        }

        $ns = substr($classname, 0, $i);
        if ($ns[0] == '\\') {
            $ns = substr($ns, 1);
        }

        if (isset($this->loaded[$ns])) {
            return false;
        }

        $home = $this->app->paths->home;
        if (isset($this->kernel[$ns])) {
            $filename = $home . $this->kernel[$ns];
            if (file_exists($filename)) {
                return $filename;
            }
        }

        if (isset($this->namespaces[$ns])) {
            $dir = $home . $this->namespaces[$ns] . '/';
            $filename = $dir . 'kernel.php';
            if (file_exists($filename)) {
                $this->loaded[$ns] = $dir;
                return $filename;
            }
        }

        $names = explode('\\', $ns);
        $vendor = array_shift($names);
        while (count($names)) {
            if (isset($this->namespaces[$vendor])) {
                $dir = $home . $this->namespaces[$vendor] . '/' . implode('/', $names) . '/';
                $filename = $dir . 'kernel.php';
                if (file_exists($filename)) {
                    $this->loaded[$ns] = $dir;
                    return $filename;
                }
            }

            $vendor.= '\\' . array_shift($names);
        }

        $dir = $home . $ns . '/';
        $filename = $dir . 'kernel.php';
        if (file_exists($filename)) {
            $this->loaded[$ns] = $dir;
            return $filename;
        }

        return false;
    }

    public function subSpace($namespace, $root)
    {
        return str_replace('\\', DIRECTORY_SEPARATOR, strtolower(substr($namespace, strlen($root) + 1)));
    }

    public function exists($class)
    {
        return isset($this->items[$class]);
    }

    public function rename($oldclass, $newclass)
    {
        if (isset($this->items[$oldclass])) {
            $this->items[$newclass] = $this->items[$oldclass];
            unset($this->items[$oldclass]);
            if (isset($this->kernel[$oldclass])) {
                $this->kernel[$newclass] = $this->items[$oldclass];
                unset($this->kernel[$oldclass]);
            }

            $this->getApp()->router->db->update('class =' . Str::quote($newclass) , 'class = ' . Str::quote($oldclass));
            $this->save();
            $this->onrename($oldclass, $newclass);
        }
    }

    public function getResourcedir($c)
    {
        $reflector = new \ReflectionClass($c);
        $filename = $reflector->getFileName();
        return dirname($filename) . '/resource/';
    }
    public function loadComposer($classToAutoLoad)
    {
        require_once ($this->getApp()->paths->home . 'vendor/autoload.php');
        if ($classToAutoLoad && ($a = spl_autoload_functions())) {
            $compclass = 'Composer\Autoload\ClassLoader';
            foreach ($a as $item) {
                if (is_array($item) && is_a($item[0], $compclass)) {
                    return call_user_func_array($item, [$classToAutoLoad]);
                }
            }
        }
    }

}

//CoEvents.php
namespace litepubl\core;

class CoEvents extends Events
{
    protected $owner;
    protected $callbacks;

    public function __construct()
    {
        $args = func_get_args();
        if (isset($args[0])) {
            if (is_array($args[0])) {
                $this->callbacks = array_shift($args);
                $this->trigger_callback('construct');
            } else if (($owner = array_shift($args)) && is_object($owner) && ($owner instanceof Data)) {
                $this->setowner($owner);
            }
        }

        if (is_array($this->eventnames)) {
            array_splice($this->eventnames, count($this->eventnames) , 0, $args);
        } else {
            $this->eventnames = $args;
        }

        parent::__construct();
    }

    public function setOwner(data $owner)
    {
        $this->owner = $owner;
        if (!isset($owner->data['events'])) {
            $owner->data['events'] = array();
        }

        $this->events = & $owner->data['events'];
    }

    public function trigger_callback($name)
    {
        if (isset($this->callbacks[$name])) {
            $callback = $this->callbacks[$name];
            if (is_callable($callback)) {
                $callback($this);
            }
        }
    }

    public function __destruct()
    {
        parent::__destruct();
        unset($this->owner, $this->callbacks);
    }

    public function assignmap()
    {
        if (!$this->owner) {
            parent::assignmap();
        }

        $this->trigger_callback('assignmap');
    }

    protected function create()
    {
        if (!$this->owner) {
            parent::create();
        }

        $this->trigger_callback('create');
    }

    public function load()
    {
        if (!$this->owner) {
            return parent::load();
        }
    }

    public function afterload()
    {
        if ($this->owner) {
            $this->events = & $this->owner->data['events'];
        } else {
            parent::afterload();
        }

        $this->trigger_callback('afterload');
    }

    public function save()
    {
        if ($this->owner) {
            return $this->owner->save();
        } else {
            return parent::save();
        }
    }

    public function inject_events()
    {
        $a = func_get_args();
        array_splice($this->eventnames, count($this->eventnames) , 0, $a);
    }

}

//Context.php
namespace litepubl\core;

class Context
{
    public $request;
    public $response;
    public $model;
    public $view;
    public $itemRoute;
    public $abtest;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function __get($name)
    {
        if (strtolower($name) == 'id') {
            return (int)$this->itemRoute['arg'];
        }

        throw new PropException(get_class($this) , $name);
    }

    public function checkAttack()
    {
        if ($this->request->checkAttack()) {
            $errorPages = new ErrorPages();
            $this->response->cache = false;
            $this->response->body = $errorPages->attack($this->request->url);
            return true;
        }

        return false;
    }

}

//Controller.php
namespace litepubl\core;

use litepubl\pages\Redirector;
use litepubl\view\MainView;

class Controller
{
    use AppTrait;

    public $cache;

    public function __construct()
    {
        $options = $this->getApp()->options;
        $this->cache = isset($options->cache) && $options->cache && !$options->adminFlag;
    }

    public function request(Context $context)
    {
        if ($this->cached($context)) {
            return;
        }

        if ($context->itemRoute) {
            if (class_exists($context->itemRoute['class'])) {
                $context->model = $this->getModel($context->itemRoute['class'], $context->itemRoute['arg']);
                $this->render($context);
            } else {
                $this->getApp()->getLogger()->warning('Class for requested item not found', $context->itemRoute);
                $this->renderStatus($context);
            }
        } elseif ($context->model) {
            $this->render($context);
        } elseif ($context->response->body) {
            $context->response->send();
        } else {
            $this->renderStatus($context);
        }
    }

    public function render(Context $context)
    {
        if (!$context->view && !($context->view = $this->findView($context))) {
            throw new \RuntimeException('View not found form model');
        }

        $context->view->request($context);
        $response = $context->response;
        if (!$response->body && $response->status == 200) {
            MainView::i()->render($context);
        }

        $response->send();
        if ($this->cache && $response->cache) {
            $this->getApp()->cache->savePhp($this->getCacheFileName($context) , $response->getString());
        }
    }

    public function findView(Context $context)
    {
        $model = $context->model;
        if ($model instanceof ResponsiveInterface) {
            return $model;
        } elseif (isset($model->view) && ($view = $model->view) && ($view instanceof ResponsiveInterface)) {
            return $view;
        }

        return false;
    }

    public function cached(Context $context)
    {
        if (!$this->cache) {
            return false;
        }

        $filename = $this->getCacheFileName($context);
        return $this->getApp()->cache->includePhp($filename);
    }

    public function getCacheFileName(Context $context)
    {
        $ext = $context->abtest ? sprintf('.%s.php', $context->abtest) : '.php';

        if (!$context->itemRoute) {
            return md5($context->request->url) . $ext;
        } else {
            switch ($context->itemRoute['type']) {
                case 'usernormal':
                case 'userget':
                    return sprintf('%s-%d%s', md5($context->request->url) , $this->getApp()->options->user, $ext);

                default:
                    return md5($context->request->url) . $ext;
            }
        }
    }

    public function url2cacheFile($url)
    {
        return md5($url) . '.php';
    }

    public function getModel($class, $arg)
    {
        if (is_a($class, __NAMESPACE__ . '\Item', true)) {
            return $class::i($arg);
        } else {
            return $this->getApp()->classes->getInstance($class);
        }
    }

    public function renderStatus(Context $context)
    {
        $response = $context->response;
        if (!$response->isRedir()) {
            $redir = Redirector::i();
            if ($url = $redir->get($context->request->url)) {
                $response->redir($url);
            }
        }

        if ($response->status == 200) {
            $response->status = 404;
        }

        $cache = $this->getApp()->cache;
        switch ($response->status) {
            case 404:
                $errorPages = new ErrorPages();
                $content = $errorPages->notfound();
                if ($this->cache && $response->cache) {
                    $cache->savePhp($this->getCacheFileName($context) , $content);
                }
                break;


            case 403:
                $errorPages = new ErrorPages();
                $content = $errorPages->forbidden();
                if ($this->cache && $response->cache) {
                    $cache->savePhp($this->getCacheFileName($context) , $content);
                }
                break;


            default:
                $response->send();
                if ($this->cache && $response->cache) {
                    $cache->savePhp($this->getCacheFileName($context) , $response->getString());
                }
        }
    }

}

//Cron.php
namespace litepubl\core;

use litepubl\Config;
use litepubl\utils\Mailer;

class Cron extends Events implements ResponsiveInterface
{
    public static $pinged = false;
    public $disableadd;
    private $socket;

    protected function create()
    {
        parent::create();
        $this->basename = 'cron';
        $this->addevents('added', 'deleted');
        $this->data['password'] = '';
        $this->data['path'] = '';
        $this->data['disableping'] = false;
        $this->cache = false;
        $this->disableadd = false;
        $this->table = 'cron';
    }

    protected function getUrl()
    {
        return sprintf('/croncron.htm%scronpass=%s', $this->getApp()->site->q, urlencode($this->password));
    }

    public function getLockpath()
    {
        if ($result = $this->path) {
            if (is_dir($result)) {
                return $result;
            }

        }
        return $this->getApp()->paths->data;
    }

    public function request(Context $context)
    {
        if (!isset($_GET['cronpass']) || ($this->password != $_GET['cronpass'])) {
            $context->response->status = 403;
            return;
        }

        if (($fh = @fopen($this->lockpath . 'cron.lok', 'w')) && flock($fh, LOCK_EX | LOCK_NB)) {
            try {
                set_time_limit(300);
                if (Config::$debug) {
                    ignore_user_abort(true);
                } else {
                    $this->getApp()->router->close_connection();
                }

                if (ob_get_level()) ob_end_flush();
                flush();

                $this->sendexceptions();
                $this->log("started loop");
                $this->execute();
            }
            catch(\Exception $e) {
                $this->getApp()->logException($e);
            }
            flock($fh, LOCK_UN);
            fclose($fh);
            @chmod($this->lockpath . 'cron.lok', 0666);
            $this->log("finished loop");
            return 'Ok';
        }
        return 'locked';
    }

    public function run()
    {
        if (ob_get_level()) ob_end_flush();
        flush();

        if (($fh = @fopen($this->lockpath . 'cron.lok', 'w')) && flock($fh, LOCK_EX | LOCK_NB)) {
            set_time_limit(300);

            try {
                $this->execute();
            }
            catch(\Exception $e) {
                $this->getApp()->logException($e);
            }

            flock($fh, LOCK_UN);
            fclose($fh);
            @chmod($this->lockpath . 'cron.lok', 0666);
            return true;
        }

        return false;
    }

    public function execute()
    {
        while ($item = $this->db->getassoc(sprintf("date <= '%s' order by date asc limit 1", Str::sqlDate()))) {
            extract($item);
            $this->log("task started:\n{$class}->{$func}($arg)");
            $arg = unserialize($arg);
            if ($class == '') {
                if (function_exists($func)) {
                    try {
                        $func($arg);
                    }
                    catch(\Exception $e) {
                        $this->getApp()->logException($e);
                    }
                } else {
                    $this->db->iddelete($id); {
                        continue;
                    }

                }
            } elseif (class_exists($class)) {
                try {
                    $obj = static ::iGet($class);
                    $obj->$func($arg);
                }
                catch(\Exception $e) {
                    $this->getApp()->logException($e);
                }
            } else {
                $this->db->iddelete($id); {
                    continue;
                }

            }
            if ($type == 'single') {
                $this->db->iddelete($id);
            } else {
                $this->db->setvalue($id, 'date', Str::sqlDate(strtotime("+1 $type")));
            }
        }
    }

    public function add($type, $class, $func, $arg = null)
    {
        if (!preg_match('/^single|hour|day|week$/', $type)) $this->error("Unknown cron type $type");
        if ($this->disableadd) {
            return false;
        }

        $id = $this->doadd($type, $class, $func, $arg);

        if (($type == 'single') && !$this->disableping && !static ::$pinged) {
            if (Config::$debug) {
                $this->getApp()->getLogger()->info("cron added $id");
            }

            $memvars = Memvars::i();
            if (!$memvars->singlecron) {
                $memvars->singlecron = time() + 300;
            }
        }

        return $id;
    }

    protected function doadd($type, $class, $func, $arg)
    {
        $id = $this->db->add(array(
            'date' => Str::sqlDate() ,
            'type' => $type,
            'class' => $class,
            'func' => $func,
            'arg' => serialize($arg)
        ));

        $this->added($id);
        return $id;
    }

    public function addnightly($class, $func, $arg)
    {
        $id = $this->db->add(array(
            'date' => date('Y-m-d 03:15:00', time()) ,
            'type' => 'day',
            'class' => $class,
            'func' => $func,
            'arg' => serialize($arg)
        ));
        $this->added($id);
        return $id;
    }

    public function addweekly($class, $func, $arg)
    {
        $id = $this->db->add(array(
            'date' => date('Y-m-d 03:15:00', time()) ,
            'type' => 'week',
            'class' => $class,
            'func' => $func,
            'arg' => serialize($arg)
        ));

        $this->added($id);
        return $id;
    }

    public function delete($id)
    {
        $this->db->iddelete($id);
        $this->deleted($id);
    }

    public function deleteclass($c)
    {
        $class = static ::get_class_name($c);
        $this->db->delete("class = '$class'");
    }

    public static function pingonshutdown()
    {
        if (static ::$pinged) {
            return;
        }

        static ::$pinged = true;

        register_shutdown_function(array(
            static ::i() ,
            'ping'
        ));
    }

    public function ping()
    {
        $p = parse_url($this->getApp()->site->url . $this->url);
        $this->pinghost($p['host'], $p['path'] . (empty($p['query']) ? '' : '?' . $p['query']));
    }

    private function pinghost($host, $path)
    {
        //$this->log("pinged host $host$path");
        if ($this->socket = @fsockopen($host, 80, $errno, $errstr, 0.10)) {
            fputs($this->socket, "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n");
            //0.01 sec
            usleep(10000);
        }
    }

    public function sendexceptions()
    {
        $filename = $this->getApp()->paths->data . 'logs' . DIRECTORY_SEPARATOR . 'exceptionsmail.log';
        if (!file_exists($filename)) {
            return;
        }

        $time = @filectime($filename);
        if (($time === false) || ($time + 3600 > time())) {
            return;
        }

        $s = file_get_contents($filename);
        $this->getApp()->storage->delete($filename);
        Mailer::SendAttachmentToAdmin('[error] ' . $this->getApp()->site->name, 'See attachment', 'errors.txt', $s);
        sleep(2);
    }

    public function log($s)
    {
        echo date('r') . "\n$s\n\n";
        flush();
        if (Config::$debug) {
            if (Config::$debug) {
                $this->getApp()->getLogger()->info($s);
            }

        }
    }

}

//Data.php
namespace litepubl\core;

class Data
{
    const ZERODATE = '0000-00-00 00:00:00';
    public static $guid = 0;
    public $basename;
    public $cache;
    public $data;
    public $coclasses;
    public $coinstances;
    public $lockcount;
    public $table;

    public static function i()
    {
        return static ::iGet(get_called_class());
    }

    public static function iGet($class)
    {
        return static ::getAppInstance()->classes->getInstance($class);
    }

    public static function getAppInstance()
    {
        return litepubl::$app;
    }

    public function __construct()
    {
        $this->lockcount = 0;
        $this->cache = true;
        $this->data = array();
        $this->coinstances = array();
        $this->coclasses = array();

        if (!$this->basename) {
            $class = get_class($this);
            $this->basename = substr($class, strrpos($class, '\\') + 1);
        }

        $this->create();
    }

    protected function create()
    {
        $this->createData();
    }

    //method to override in traits when in base class declared create method
    protected function createData()
    {
    }

    public function __get($name)
    {
        if (method_exists($this, $get = 'get' . $name)) {
            return $this->$get();
        } elseif (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            foreach ($this->coinstances as $coinstance) {
                if (isset($coinstance->$name)) {
                    return $coinstance->$name;
                }
            }

            throw new PropException(get_class($this) , $name);
        }
    }

    public function __set($name, $value)
    {
        if (method_exists($this, $set = 'set' . $name)) {
            $this->$set($value);
        } elseif (key_exists($name, $this->data)) {
            $this->data[$name] = $value;
        } else {
            foreach ($this->coinstances as $coinstance) {
                if (isset($coinstance->$name)) {
                    $coinstance->$name = $value;
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    public function __call($name, $params)
    {
        if (method_exists($this, strtolower($name))) {
            return call_user_func_array(array(
                $this,
                strtolower($name)
            ) , $params);
        }

        foreach ($this->coinstances as $coinstance) {
            if (method_exists($coinstance, $name) || $coinstance->method_exists($name)) {
                return call_user_func_array(array(
                    $coinstance,
                    $name
                ) , $params);
            }
        }

        $this->error("The requested method $name not found in class " . get_class($this));
    }

    public function __isset($name)
    {
        if (array_key_exists($name, $this->data) || method_exists($this, "get$name") || method_exists($this, "Get$name")) {
            return true;
        }

        foreach ($this->coinstances as $coinstance) {
            if (isset($coinstance->$name)) {
                return true;
            }
        }

        return false;
    }

    public function method_exists($name)
    {
        return false;
    }

    public function error($Msg, $code = 0)
    {
        throw new \Exception($Msg, $code);
    }

    public function getBaseName()
    {
        return $this->basename;
    }

    public function getApp()
    {
        return static ::getAppInstance();
    }

    public function install()
    {
        $this->externalchain('Install');
    }

    public function uninstall()
    {
        $this->externalchain('Uninstall');
    }

    public function validate($repair = false)
    {
        $this->externalchain('Validate', $repair);
    }

    protected function externalChain($func, $arg = null)
    {
        $parents = class_parents($this);
        array_splice($parents, 0, 0, get_class($this));
        foreach ($parents as $class) {
            $this->externalFunc($class, $func, $arg);
        }
    }

    public function getExternalFuncName($class, $func)
    {
        $reflector = new \ReflectionClass($class);
        $filename = $reflector->getFileName();

        if (strpos($filename, 'kernel.')) {
            $filename = dirname($filename) . DIRECTORY_SEPARATOR . basename(str_replace('\\', DIRECTORY_SEPARATOR, $class)) . '.php';
        }

        $externalname = basename($filename, '.php') . '.install.php';
        $dir = dirname($filename) . DIRECTORY_SEPARATOR;
        $file = $dir . 'install' . DIRECTORY_SEPARATOR . $externalname;
        if (!file_exists($file)) {
            $file = $dir . $externalname;
            if (!file_exists($file)) {
                return false;
            }
        }

        include_once ($file);

        $fnc = $class . $func;
        if (function_exists($fnc)) {
            return $fnc;
        }

        return false;
    }

    public function externalFunc($class, $func, $args)
    {
        if ($fnc = $this->getExternalFuncName($class, $func)) {
            if (is_array($args)) {
                array_unshift($args, $this);
            } else {
                $args = array(
                    $this,
                    $args
                );
            }

            return \call_user_func_array($fnc, $args);
        }
    }

    public function getStorage()
    {
        return $this->getApp()->storage;
    }

    public function load()
    {
        if ($this->getStorage()->load($this)) {
            $this->afterLoad();
            return true;
        }

        return false;
    }

    public function save()
    {
        if ($this->lockcount) {
            return;
        }

        return $this->getStorage()->save($this);
    }

    public function afterload()
    {
        foreach ($this->coinstances as $coinstance) {
            if (method_exists($coinstance, 'afterload')) {
                $coinstance->afterload();
            }
        }
    }

    public function lock()
    {
        $this->lockcount++;
    }

    public function unlock()
    {
        if (--$this->lockcount <= 0) {
            $this->save();
        }
    }

    public function getLocked()
    {
        return $this->lockcount > 0;
    }

    public function Getclass()
    {
        return get_class($this);
    }

    public function getDbversion()
    {
        return false;

    }

    public function getDb($table = '')
    {
        $table = $table ? $table : $this->table;
        if ($table) {
            $this->getApp()->db->table = $table;
        }

        return $this->getApp()->db;
    }

    protected function getThistable()
    {
        return $this->getApp()->db->prefix . $this->table;
    }

    public static function get_class_name($c)
    {
        return is_object($c) ? get_class($c) : trim($c);
    }

    public static function encrypt($s, $key)
    {
        $maxkey = mcrypt_get_key_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
        if (strlen($key) > $maxkey) {
            $key = substr($key, $maxkey);
        }

        $block = mcrypt_get_block_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
        $pad = $block - (strlen($s) % $block);
        $s.= str_repeat(chr($pad) , $pad);
        return mcrypt_encrypt(MCRYPT_Blowfish, $key, $s, MCRYPT_MODE_ECB);
    }

    public static function decrypt($s, $key)
    {
        $maxkey = mcrypt_get_key_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
        if (strlen($key) > $maxkey) {
            $key = substr($key, $maxkey);
        }

        $s = mcrypt_decrypt(MCRYPT_Blowfish, $key, $s, MCRYPT_MODE_ECB);
        $len = strlen($s);
        $pad = ord($s[$len - 1]);
        return substr($s, 0, $len - $pad);
    }

}

//DB.php
namespace litepubl\core;

use litepubl\Config;

class DB
{
    use AppTrait;
    use Singleton;

    public $mysqli;
    public $result;
    public $sql;
    public $dbname;
    public $table;
    public $prefix;
    public $history;

    public function __construct()
    {
        $this->sql = '';
        $this->table = '';
        $this->history = array();

        $this->setconfig($this->getconfig());
    }

    public function getConfig()
    {
        if (config::$db) {
            return config::$db;
        }

        $options = $this->getApp()->options;
        if (isset($options->dbconfig)) {
            $result = $options->dbconfig;
            //decrypt db password
            $result['password'] = $options->dbpassword;
            return $result;
        }

        return false;
    }

    public function setConfig($dbconfig)
    {
        if (!$dbconfig) {
            return false;
        }

        $this->dbname = $dbconfig['dbname'];
        $this->prefix = $dbconfig['prefix'];

        $this->mysqli = new \mysqli($dbconfig['host'], $dbconfig['login'], $dbconfig['password'], $dbconfig['dbname'], $dbconfig['port'] > 0 ? $dbconfig['port'] : null);

        if (mysqli_connect_error()) {
            throw new \Exception('Error connect to database');
        }

        $this->mysqli->set_charset('utf8');
        //$this->query('SET NAMES utf8');
        if (Config::$enableZeroDatetime) {
            $this->enableZeroDatetime();
        }

        /* lost performance
        $timezone = date('Z') / 3600;
        if ($timezone > 0) $timezone = "+$timezone";
        $this->query("SET time_zone = '$timezone:00'");
        */
    }
    /*
    public function __destruct() {
    if (is_object($this)) {
      if (is_object($this->mysqli)) $this->mysqli->close();
      $this->mysqli = false;
    }
    }
    */
    public function __get($name)
    {
        if ($name == 'man') {
            return DBManager::i();
        }

        return $this->prefix . $name;
    }

    public function exec($sql)
    {
        return $this->query($sql);
    }

    public function query($sql)
    {
        $this->sql = $sql;
        if (Config::$debug) {
            $this->history[] = array(
                'sql' => $sql,
                'time' => 0
            );
            $microtime = microtime(true);
        }

        if (is_object($this->result)) {
            $this->result->close();
        }

        $this->result = $this->mysqli->query($sql);
        if ($this->result == false) {
            $this->logError($this->mysqli->error);
        } elseif (Config::$debug) {
            $this->history[count($this->history) - 1]['time'] = microtime(true) - $microtime;
            if ($this->mysqli->warning_count && ($r = $this->mysqli->query('SHOW WARNINGS'))) {
                $this->getApp()->getLogger()->warning($sql, $r->fetch_assoc());
            }
        }

        return $this->result;
    }

    protected function logError($mesg)
    {
        $log = "exception:\n$mesg\n$this->sql\n";
        $app = $this->getApp();
        $log.= $app->getLogManager()->getTrace();
        $log.= $this->performance();
        die($log);
        //$app->getLogger()->alert($log);
        throw new \Exception($log);
    }

    public function performance()
    {
        $result = '';
        $total = 0.0;
        $max = 0.0;
        foreach ($this->history as $i => $item) {
            $result.= "$i: {$item['time']}\n{$item['sql']}\n\n";
            $total+= $item['time'];
            if ($max < $item['time']) {
                $maxsql = $item['sql'];
                $max = $item['time'];
            }
        }
        $result.= "maximum $max\n$maxsql\n";
        $result.= sprintf("%s total time\n%d querries\n\n", $total, count($this->history));
        return $result;
    }

    public function quote($s)
    {
        return sprintf('\'%s\'', $this->mysqli->real_escape_string($s));
    }

    public function escape($s)
    {
        return $this->mysqli->real_escape_string($s);
    }

    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    public function select($where)
    {
        if ($where != '') $where = 'where ' . $where;
        return $this->query("SELECT * FROM $this->prefix$this->table $where");
    }

    public function idSelect($where)
    {
        return $this->res2id($this->query("select id from $this->prefix$this->table where $where"));
    }

    public function selectAssoc($sql)
    {
        return $this->query($sql)->fetch_assoc();
    }

    public function getAssoc($where)
    {
        return $this->select($where)->fetch_assoc();
    }

    public function update($values, $where)
    {
        return $this->query("update $this->prefix$this->table set $values   where $where");
    }

    public function idUpdate($id, $values)
    {
        return $this->update($values, "id = $id");
    }

    public function assoc2update(array $a)
    {
        $list = array();
        foreach ($a As $name => $value) {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
                $list[] = sprintf('%s = %s ', $name, $value); {
                    continue;
                }
            }

            $list[] = sprintf('%s = %s', $name, $this->quote($value));
        }

        return implode(', ', $list);
    }

    public function updateAssoc(array $a, $index = 'id')
    {
        $id = $a[$index];
        unset($a[$index]);
        return $this->update($this->assoc2update($a) , "$index = '$id' limit 1");
    }

    public function setValues($id, array $values)
    {
        return $this->update($this->assoc2update($values) , "id = '$id' limit 1");
    }

    public function insertRow($row)
    {
        return $this->query(sprintf('INSERT INTO %s%s %s', $this->prefix, $this->table, $row));
    }

    public function insertAssoc(array $a)
    {
        unset($a['id']);
        return $this->add($a);
    }

    public function addUpdate(array $a)
    {
        if ($this->idexists($a['id'])) {
            $this->updateAssoc($a);
        } else {
            return $this->add($a);
        }
    }

    public function add(array $a)
    {
        $this->insertRow($this->assocToRow($a));
        if ($id = $this->mysqli->insert_id) {
            return $id;
        }

        $r = $this->query('select last_insert_id() from ' . $this->prefix . $this->table)->fetch_row();
        return (int)$r[0];
    }

    public function insert(array $a)
    {
        $this->insertRow($this->assocToRow($a));
    }

    public function assocToRow(array $a)
    {
        $vals = array();
        foreach ($a as $val) {
            if (is_bool($val)) {
                $vals[] = $val ? '1' : '0';
            } else {
                $vals[] = $this->quote($val);
            }
        }

        return sprintf('(%s) values (%s)', implode(', ', array_keys($a)) , implode(', ', $vals));
    }

    public function getCount($where = '')
    {
        $sql = "SELECT COUNT(*) as count FROM $this->prefix$this->table";
        if ($where) $sql.= ' where ' . $where;
        if (($res = $this->query($sql)) && ($r = $res->fetch_assoc())) {
            return (int)$r['count'];
        }
        return false;
    }

    public function delete($where)
    {
        return $this->query("delete from $this->prefix$this->table where $where");
    }

    public function idDelete($id)
    {
        return $this->query("delete from $this->prefix$this->table where id = $id");
    }

    public function deleteItems(array $items)
    {
        return $this->delete('id in (' . implode(', ', $items) . ')');
    }

    public function idExists($id)
    {
        if ($r = $this->query("select id  from $this->prefix$this->table where id = $id limit 1")) {
            return $r && $r->fetch_assoc();
        }

        return false;
    }

    public function exists($where)
    {
        return $this->query("select *  from $this->prefix$this->table where $where limit 1")->num_rows;
    }

    public function getList(array $list)
    {
        return $this->res2assoc($this->select(sprintf('id in (%s)', implode(',', $list))));
    }

    public function getItems($where)
    {
        return $this->res2assoc($this->select($where));
    }

    public function getItem($id, $propname = 'id')
    {
        if ($r = $this->query("select * from $this->prefix$this->table where $propname = $id limit 1")) {
            return $r->fetch_assoc();
        }

        return false;
    }

    public function findItem($where)
    {
        return $this->query("select * from $this->prefix$this->table where $where limit 1")->fetch_assoc();
    }

    public function findId($where)
    {
        return $this->findprop('id', $where);
    }

    public function findProp($propname, $where)
    {
        if ($r = $this->query("select $propname from $this->prefix$this->table where $where limit 1")->fetch_assoc()) {
            return $r[$propname];
        }

        return false;
    }

    public function getVal($table, $id, $name)
    {
        if ($r = $this->query("select $name from $this->prefix$table where id = $id limit 1")->fetch_assoc()) {
            return $r[$name];
        }

        return false;
    }

    public function getValue($id, $name)
    {
        if ($r = $this->query("select $name from $this->prefix$this->table where id = $id limit 1")->fetch_assoc()) {
            return $r[$name];
        }

        return false;
    }

    public function setValue($id, $name, $value)
    {
        return $this->update("$name = " . $this->quote($value) , "id = $id");
    }

    public function getValues($names, $where)
    {
        $result = array();
        $res = $this->query("select $names from $this->prefix$this->table where $where");
        if (is_object($res)) {
            while ($r = $res->fetch_row()) {
                $result[$r[0]] = $r[1];
            }
        }
        return $result;
    }

    public function res2array($res)
    {
        $result = array();
        if (is_object($res)) {
            while ($row = $res->fetch_row()) {
                $result[] = $row;
            }
            return $result;
        }
    }

    public function res2id($res)
    {
        $result = array();
        if (is_object($res)) {
            while ($row = $res->fetch_row()) {
                $result[] = $row[0];
            }
        }
        return $result;
    }

    public function res2assoc($res)
    {
        $result = array();
        if (is_object($res)) {
            while ($r = $res->fetch_assoc()) {
                $result[] = $r;
            }
        }
        return $result;
    }

    public function res2items($res)
    {
        $result = array();
        if (is_object($res)) {
            while ($r = $res->fetch_assoc()) {
                $result[(int)$r['id']] = $r;
            }
        }
        return $result;
    }

    public function fetchassoc($res)
    {
        return is_object($res) ? $res->fetch_assoc() : false;
    }

    public function fetchnum($res)
    {
        return is_object($res) ? $res->fetch_row() : false;
    }

    public function countof($res)
    {
        return is_object($res) ? $res->num_rows : 0;
    }

    public function enableZeroDatetime()
    {
        //use mysqli to prevent strange warnings
        $v = $this->fetchassoc($this->mysqli->query('show variables like \'sql_mode\''));
        $a = explode(',', $v['Value']);
        $ex = ['NO_ZERO_IN_DATE', 'NO_ZERO_DATE'];
        $a = array_diff($a, $ex);
        $v = implode(',', $a);
        $this->mysqli->query("set sql_mode = '$v'");
    }
}

//ErrorPages.php
namespace litepubl\core;

use litepubl\pages\Forbidden;
use litepubl\pages\Notfound404;
use litepubl\view\Lang;
use litepubl\view\MainView;

class ErrorPages
{
    use AppTrait;

    public $cache;

    public function __construct()
    {
        $options = $this->getApp()->options;
        $this->cache = $options->cache && !$options->adminFlag;
    }

    public function notfound()
    {
        $filename = '404.php';
        if ($this->cache && ($result = $this->getApp()->cache->getString($filename))) {
            eval('?>' . $result);
            return $result;
        }

        $instance = Notfound404::i();
        $context = new Context(new Request('', '') , new Response());
        $context->model = $instance;
        $context->view = $instance;
        $instance->request($context);
        MainView::i()->render($context);
        $context->response->send();

        if ($this->cache) {
            $result = $context->response->getString();
            $this->getApp()->cache->savePhp($filename, $result);
            return $result;
        }
    }

    public function forbidden()
    {
        $filename = '403.php';
        if ($this->cache && ($result = $this->getApp()->cache->getString($filename))) {
            eval('?>' . $result);
            return $result;
        }

        $instance = Forbidden::i();
        $context = new Context(new Request('', '') , new Response());
        $context->model = $instance;
        $instance->request($context);
        MainView::i()->render($context);
        $context->response->send();

        if ($this->cache) {
            $result = $context->response->getString();
            $this->getApp()->cache->savePhp($filename, $result);
            return $result;
        }
    }

    public function attack($url)
    {
        Lang::usefile('admin');
        if ($_POST) {
            return Lang::get('login', 'xxxattack');
        }

        return Lang::get('login', 'confirmxxxattack') . sprintf(' <a href="%1$s">%1$s</a>', $url);
    }

}

//Events.php
namespace litepubl\core;

class Events extends Data
{
    protected $events;
    protected $eventnames;
    protected $map;

    public function __construct()
    {
        if (!is_array($this->eventnames)) {
            $this->eventnames = array();
        }

        if (!is_array($this->map)) {
            $this->map = array();
        }

        parent::__construct();

        $this->assignmap();
        $this->load();
    }

    public function __destruct()
    {
        unset($this->data, $this->events, $this->eventnames, $this->map);
    }

    protected function create()
    {
        parent::create();
        $this->addmap('events', array());
        $this->addmap('coclasses', array());
    }

    public function assignMap()
    {
        foreach ($this->map as $propname => $key) {
            $this->$propname = & $this->data[$key];
        }
    }

    public function afterLoad()
    {
        $this->assignmap();

        foreach ($this->coclasses as $coclass) {
            $this->coinstances[] = static ::iGet($coclass);
        }

        parent::afterload();
    }

    protected function addMap($name, $value)
    {
        $this->map[$name] = $name;
        $this->data[$name] = $value;
        $this->$name = & $this->data[$name];
    }

    public function free()
    {
        unset($this->getApp()->classes->instances[get_class($this) ]);
        foreach ($this->coinstances as $coinstance) {
            $coinstance->free();
        }
    }

    public function eventExists($name)
    {
        return in_array($name, $this->eventnames);
    }

    public function __get($name)
    {
        if (method_exists($this, $name)) {
            return array(
                get_class($this) ,
                $name
            );
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (parent::__set($name, $value)) {
            return true;
        }

        if (in_array($name, $this->eventnames)) {
            $this->addevent($name, $value[0], $value[1]);
            return true;
        }
        $this->error(sprintf('Unknown property %s in class %s', $name, get_class($this)));
    }

    public function method_exists($name)
    {
        return in_array($name, $this->eventnames);
    }

    public function __call($name, $params)
    {
        if (in_array($name, $this->eventnames)) {
            return $this->callevent($name, $params);
        }

        parent::__call($name, $params);
    }

    public function __isset($name)
    {
        return parent::__isset($name) || in_array($name, $this->eventnames);
    }

    protected function addEvents()
    {
        $a = func_get_args();
        array_splice($this->eventnames, count($this->eventnames) , 0, $a);
    }

    public function callEvent($name, $params)
    {
        if (!isset($this->events[$name])) {
            return '';
        }

        $result = '';
        foreach ($this->events[$name] as $i => $item) {
            //backward compability
            $class = isset($item[0]) ? $item[0] : (isset($item['class']) ? $item['class'] : '');

            if (is_string($class) && class_exists($class)) {
                $call = array(
                    static ::iGet($class) ,
                    isset($item[1]) ? $item[1] : $item['func']
                );
            } elseif (is_object($class)) {
                $call = array(
                    $class,
                    isset($item[1]) ? $item[1] : $item['func']
                );
            } else {
                $call = false;
            }

            if ($call) {
                try {
                    $result = call_user_func_array($call, $params);
                }
                catch(CancelEvent $e) {
                    return $e->result;
                }

                // 2 index = once
                if (isset($item[2]) && $item[2]) {
                    array_splice($this->events[$name], $i, 1);
                }

            } else {
                //class not found and delete event handler
                array_splice($this->events[$name], $i, 1);
                if (!count($this->events[$name])) {
                    unset($this->events[$name]);
                }

                $this->save();
            }
        }

        return $result;
    }

    public static function cancelEvent($result)
    {
        throw new CancelEvent($result);
    }

    public function setEvent($name, $params)
    {
        return $this->addevent($name, $params['class'], $params['func']);
    }

    public function addEvent($name, $class, $func, $once = false)
    {
        if (!in_array($name, $this->eventnames)) {
            return $this->error(sprintf('No such %s event', $name));
        }

        if (empty($class)) {
            $this->error("Empty class name to bind $name event");
        }

        if (empty($func)) {
            $this->error("Empty function name to bind $name event");
        }

        if (!isset($this->events[$name])) {
            $this->events[$name] = array();
        }

        //check if event already added
        foreach ($this->events[$name] as $event) {
            if (isset($event[0]) && $event[0] == $class && $event[1] == $func) {
                return false;
                //backward compability
                
            } elseif (isset($event['class']) && $event['class'] == $class && $event['func'] == $func) {
                return false;
            }
        }

        if ($once) {
            $this->events[$name][] = array(
                $class,
                $func,
                true
            );
        } else {
            $this->events[$name][] = array(
                $class,
                $func
            );
            $this->save();
        }
    }

    public function delete_event_class($name, $class)
    {
        if (!isset($this->events[$name])) {
            return false;
        }

        $list = & $this->events[$name];
        $deleted = false;
        for ($i = count($list) - 1; $i >= 0; $i--) {
            $event = $list[$i];

            if ((isset($event[0]) && $event[0] == $class) ||
            //backward compability
            (isset($event['class']) && $event['class'] == $class)) {
                array_splice($list, $i, 1);
                $deleted = true;
            }
        }

        if ($deleted) {
            if (count($list) == 0) {
                unset($this->events[$name]);
            }

            $this->save();
        }

        return $deleted;
    }

    public function unsubscribeclass($obj)
    {
        $this->unbind($obj);
    }

    public function unsubscribeclassname($class)
    {
        $this->unbind($class);
    }

    public function unbind($c)
    {
        $class = static ::get_class_name($c);
        foreach ($this->events as $name => $events) {
            foreach ($events as $i => $item) {
                if ((isset($item[0]) && $item[0] == $class) || (isset($item['class']) && $item['class'] == $class)) {
                    array_splice($this->events[$name], $i, 1);
                }
            }
        }

        $this->save();
    }

    public function setEventorder($eventname, $c, $order)
    {
        if (!isset($this->events[$eventname])) {
            return false;
        }

        $events = & $this->events[$eventname];
        $class = static ::get_class_name($c);
        $count = count($events);
        if (($order < 0) || ($order >= $count)) {
            $order = $count - 1;
        }

        foreach ($events as $i => $event) {
            if ((isset($event[0]) && $class == $event[0]) || (isset($event['class']) && $class == $event['class'])) {
                if ($i == $order) {
                    return true;
                }

                array_splice($events, $i, 1);
                array_splice($events, $order, 0, array(
                    0 => $event
                ));

                $this->save();
                return true;
            }
        }
    }

    private function indexofcoclass($class)
    {
        return array_search($class, $this->coclasses);
    }

    public function addcoclass($class)
    {
        if ($this->indexofcoclass($class) === false) {
            $this->coclasses[] = $class;
            $this->save();
            $this->coinstances = static ::iGet($class);
        }
    }

    public function deletecoclass($class)
    {
        $i = $this->indexofcoclass($class);
        if (is_int($i)) {
            array_splice($this->coclasses, $i, 1);
            $this->save();
        }
    }

}

//Getter.php
namespace litepubl\core;

class Getter
{
    public $get;
    public $set;

    public function __construct($get = null, $set = null)
    {
        $this->get = $get;
        $this->set = $set;
    }

    public function __get($name)
    {
        return call_user_func_array($this->get, array(
            $name
        ));
    }

    public function __set($name, $value)
    {
        call_user_func_array($this->set, array(
            $name,
            $value
        ));
    }

}

//Item.php
namespace litepubl\core;

class Item extends Data
{
    public static $instances;

    public static function i($id = 0)
    {
        return static ::itemInstance(get_called_class() , (int)$id);
    }

    public static function itemInstance($class, $id = 0)
    {
        $name = $class::getInstanceName();
        if (!isset(static ::$instances)) {
            static ::$instances = [];
        }

        if (isset(static ::$instances[$name][$id])) {
            return static ::$instances[$name][$id];
        }

        $self = static ::getAppInstance()->classes->newItem($name, $class, $id);
        return $self->loadData($id);
    }

    public function loadData($id)
    {
        $this->data['id'] = $id;
        if ($id) {
            if (!$this->load()) {
                $this->free();
                return false;
            }

            static ::$instances[$this->instancename][$id] = $this;
        }

        return $this;
    }

    public function free()
    {
        unset(static ::$instances[$this->getinstancename() ][$this->id]);
    }

    public function __construct()
    {
        parent::__construct();
        $this->data['id'] = 0;
    }

    public function __destruct()
    {
        $this->free();
    }

    public function __set($name, $value)
    {
        if (parent::__set($name, $value)) {
            return true;
        }

        return $this->Error("Field $name not exists in class " . get_class($this));
    }

    public function setId($id)
    {
        if ($id != $this->id) {
            $name = $this->instanceName;
            if (!isset(static ::$instances)) {
                static ::$instances = array();
            }

            if (!isset(static ::$instances[$name])) {
                static ::$instances[$name] = array();
            }

            $a = & static ::$instances[$this->instanceName];
            if (isset($a[$this->id])) {
                unset($a[$this->id]);
            }

            if (isset($a[$id])) {
                $a[$id] = 0;
            }

            $a[$id] = $this;
            $this->data['id'] = $id;
        }
    }

    public function loadItem($id)
    {
        if ($id == $this->id) {
            return true;
        }

        $this->setid($id);
        if ($this->load()) {
            return true;
        }

        return false;
    }

}

//ItemOwnerTrait.php
namespace litepubl\core;

trait ItemOwnerTrait
{

    public function load()
    {
        $owner = $this->owner;
        if ($owner->itemExists($this->id)) {
            $this->data = & $owner->items[$this->id];
            $this->afterLoad();
            return true;
        }
        return false;
    }

    public function save()
    {
        return $this->owner->save();
    }

}

//Items.php
namespace litepubl\core;

class Items extends Events
{
    public $items;
    public $dbversion;
    protected $idprop;
    protected $autoid;

    protected function create()
    {
        parent::create();
        $this->addevents('added', 'deleted');
        $this->idprop = 'id';
        if ($this->dbversion) {
            $this->items = array();
        } else {
            $this->addmap('items', array());
            $this->addmap('autoid', 0);
        }
    }

    public function getStorage()
    {
        if ($this->dbversion) {
            return $this->getApp()->poolStorage;
        } else {
            return parent::getStorage();
        }
    }

    public function loadall()
    {
        if ($this->dbversion) {
            return $this->select('', '');
        }
    }

    public function loaditems(array $items)
    {
        if (!$this->dbversion) {
            return;
        }

        //exclude loaded items
        $items = array_diff($items, array_keys($this->items));
        if (count($items) == 0) {
            return;
        }

        $list = implode(',', $items);
        $this->select("$this->thistable.$this->idprop in ($list)", '');
    }

    public function select($where, $limit)
    {
        if (!$this->dbversion) {
            $this->error('Select method must be called ffrom database version');
        }

        if ($where) {
            $where = 'where ' . $where;
        }

        return $this->res2items($this->db->query("SELECT * FROM $this->thistable $where $limit"));
    }

    public function res2items($res)
    {
        if (!$res) {
            return array();
        }

        $result = array();
        $db = $this->getApp()->db;
        while ($item = $db->fetchassoc($res)) {
            $id = $item[$this->idprop];
            $result[] = $id;
            $this->items[$id] = $item;
        }

        return $result;
    }

    public function findItem($where)
    {
        $a = $this->select($where, 'limit 1');
        return count($a) ? $a[0] : false;
    }

    public function getCount()
    {
        if ($this->dbversion) {
            return $this->db->getcount();
        } else {
            return count($this->items);
        }
    }

    public function getItem($id)
    {
        if (isset($this->items[$id])) {
            return $this->items[$id];
        }

        if ($this->dbversion && $this->select("$this->thistable.$this->idprop = $id", 'limit 1')) {
            return $this->items[$id];
        }

        return $this->error(sprintf('Item %d not found in class %s', $id, get_class($this)));
    }

    public function getValue($id, $name)
    {
        if ($this->dbversion && !isset($this->items[$id])) {
            $this->items[$id] = $this->db->getitem($id, $this->idprop);
        }

        return $this->items[$id][$name];
    }

    public function setValue($id, $name, $value)
    {
        $this->items[$id][$name] = $value;
        if ($this->dbversion) {
            $this->db->update("$name = " . Str::quote($value) , "$this->idprop = $id");
        }
    }

    public function itemExists($id)
    {
        if (isset($this->items[$id])) {
            return true;
        }

        if ($this->dbversion) {
            try {
                return $this->getitem($id);
            }
            catch(\Exception $e) {
                return false;
            }
        }
        return false;
    }

    public function indexof($name, $value)
    {
        if ($this->dbversion) {
            return $this->db->findprop($this->idprop, "$name = " . Str::quote($value));
        }

        foreach ($this->items as $id => $item) {
            if ($item[$name] == $value) {
                return $id;
            }
        }
        return false;
    }

    public function additem(array $item)
    {
        $id = $this->dbversion ? $this->db->add($item) : ++$this->autoid;
        $item[$this->idprop] = $id;
        $this->items[$id] = $item;
        if (!$this->dbversion) {
            $this->save();
        }

        $this->added($id);
        return $id;
    }

    public function delete($id)
    {
        if ($this->dbversion) {
            $this->db->delete("$this->idprop = $id");
        }

        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            if (!$this->dbversion) {
                $this->save();
            }

            $this->deleted($id);
            return true;
        }
        return false;
    }

}

//ItemsPosts.php
namespace litepubl\core;

class ItemsPosts extends Items
{
    public $tablepost;
    public $postprop;
    public $itemprop;

    protected function create()
    {
        parent::create();
        $this->basename = 'itemsposts';
        $this->table = 'itemsposts';
        $this->tablepost = 'posts';
        $this->postprop = 'post';
        $this->itemprop = 'item';
    }

    public function add($idpost, $iditem)
    {
        $this->db->insert(array(
            $this->postprop => $idpost,
            $this->itemprop => $iditem
        ));
        $this->added();
    }

    public function exists($idpost, $iditem)
    {
        return $this->db->exists("$this->postprop = $idpost and $this->itemprop = $iditem");
    }

    public function remove($idpost, $iditem)
    {
        return $this->db->delete("$this->postprop = $idpost and $this->itemprop = $iditem");
    }

    public function delete($idpost)
    {
        return $this->deletepost($idpost);
    }

    public function deletepost($idpost)
    {
        $db = $this->db;
        $result = $db->res2id($db->query("select $this->itemprop from $this->thistable where $this->postprop = $idpost"));
        $db->delete("$this->postprop = $idpost");
        return $result;
    }

    public function deleteitem($iditem)
    {
        $this->db->delete("$this->itemprop = $iditem");
        $this->deleted();
    }

    public function setItems($idpost, array $items)
    {
        Arr::clean($items);
        $db = $this->db;
        $old = $this->getitems($idpost);
        $add = array_diff($items, $old);
        $delete = array_diff($old, $items);

        if (count($delete)) $db->delete("$this->postprop = $idpost and $this->itemprop in (" . implode(', ', $delete) . ')');
        if (count($add)) {
            $vals = array();
            foreach ($add as $iditem) {
                $vals[] = "($idpost, $iditem)";
            }
            $db->exec("INSERT INTO $this->thistable ($this->postprop, $this->itemprop) values " . implode(',', $vals));
        }

        return array_merge($old, $add);
    }

    public function getItems($idpost)
    {
        return $this->getApp()->db->res2id($this->getApp()->db->query("select $this->itemprop from $this->thistable where $this->postprop = $idpost"));
    }

    public function getPosts($iditem)
    {
        return $this->getApp()->db->res2id($this->getApp()->db->query("select $this->postprop from $this->thistable where $this->itemprop = $iditem"));
    }

    public function getPostscount($ititem)
    {
        $db = $this->getdb($this->tablepost);
        return $db->getcount("$db->prefix$this->tablepost.status = 'published' and id in (select $this->postprop from $this->thistable where $this->itemprop = $ititem)");
    }

    public function updateposts(array $list, $propname)
    {
        $db = $this->db;
        foreach ($list as $idpost) {
            $items = $this->getitems($idpost);
            $db->table = $this->tablepost;
            $db->setvalue($idpost, $propname, implode(', ', $items));
        }
    }

}

//MemvarMemcache.php
namespace litepubl\core;

class MemvarMemcache extends CacheMemcache
{
    public $data;

    public function __construct()
    {
        parent::__construct();
        $this->data = array();
    }

    public function getRevision()
    {
        //nothing, just to override parent method
        
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        if ($result = $this->get($name)) {
            $this->data[$name] = $result;
        }

        return $result;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
        $this->set($name, $value);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
        $this->delete($name);
    }

}

//MemvarMysql.php
namespace litepubl\core;

class MemvarMysql
{
    use appTrait;

    public $lifetime;
    public $table;
    public $data;
    private $checked;

    public function __construct()
    {
        $this->table = 'memstorage';
        $this->checked = false;
        $this->data = array();
        $this->lifetime = 10800;
    }

    public function getDb()
    {
        return $this->getApp()->db;
    }

    public function getName($name)
    {
        if (strlen($name) > 32) {
            return md5($name);
        }

        return $name;
    }

    public function __get($name)
    {
        $name = $this->getname($name);
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return $this->get($name);
    }

    public function get($name)
    {
        $result = false;
        if (!$this->checked) {
            $this->check();
        }

        $db = $this->getdb();
        if ($r = $db->query("select value from $db->prefix$this->table where name = '$name' limit 1")->fetch_assoc()) {
            $result = $this->unserialize($r['value']);
            $this->data[$name] = $result;
        }

        return $result;
    }

    public function __set($name, $value)
    {
        $name = $this->getname($name);
        $exists = isset($this->data[$name]);
        $this->data[$name] = $value;
        if (!$this->checked) {
            $this->check();
        }

        $db = $this->getdb();
        $v = $db->quote($this->serialize($value));
        if ($exists) {
            $db->query("update $db->prefix$this->table set value = $v where name = '$name' limit 1");
        } else {
            $db->query("insert into $db->prefix$this->table (name, value) values('$name', $v)");
        }
    }

    public function __unset($name)
    {
        $name = $this->getname($name);
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }

        if (!$this->checked) {
            $this->check();
        }

        $db = $this->getdb();
        $db->query("delete from $db->prefix$this->table where name = '$name' limit 1");
    }

    public function serialize($data)
    {
        return serialize($data);
    }

    public function unserialize(&$data)
    {
        return unserialize($data);
    }

    public function check()
    {
        $this->checked = true;

        //exclude throw exception
        $db = $this->getdb();
        $res = $db->mysqli->query("select value from $db->prefix$this->table where name = 'created' limit 1");
        if (is_object($res) && ($r = $res->fetch_assoc())) {
            $res->close();
            $created = $this->unserialize($r['value']);
            if ($created + $this->lifetime < time()) {
                $this->loadAll();
                $this->clear();
                $this->data['created'] = time();
                $this->saveAll();
            }
        } else {
            $this->createTable();
            $this->created = time();
        }
    }

    public function loadAll()
    {
        $db = $this->getdb();
        $res = $db->query("select * from $db->prefix$this->table");
        if (is_object($res)) {
            while ($item = $res->fetch_assoc()) {
                $this->data[$item['name']] = $this->unserialize($item['value']);
            }
        }
    }

    public function saveAll()
    {
        $db = $this->getdb();
        $a = array();
        foreach ($this->data as $name => $value) {
            $a[] = sprintf('(\'%s\',%s)', $name, $db->quote($this->serialize($value)));
        }

        $values = implode(',', $a);
        $db->query("insert into $db->prefix$this->table (name, value) values $values");
    }

    public function createTable()
    {
        $db = $this->getdb();
        $db->mysqli->query("create table if not exists $db->prefix$this->table (
    name varchar(32) not null,
    value varchar(255),
    key (name)
    )
    ENGINE=MEMORY
    DEFAULT CHARSET=utf8
    COLLATE = utf8_general_ci");
    }

    public function clear()
    {
        $db = $this->getdb();
        try {
            $db->query("truncate table $db->prefix$this->table");
        }
        catch(\Exception $e) {
        }
    }

}

//Memvars.php
namespace litepubl\core;

class Memvars
{
    use AppTrait;

    public static $vars;

    public static function i()
    {
        if (!static ::$vars) {
            if (static ::getAppInstance()->memcache) {
                static ::$vars = new MemvarMemcache();
            } else {
                static ::$vars = new MemvarMysql();
            }
        }

        return static ::$vars;
    }
}

//Options.php
namespace litepubl\core;

use litepubl\Config;

class Options extends Events
{
    use PoolStorageTrait;

    public $groupnames;
    public $parentgroups;
    public $group;
    public $idgroups;
    protected $_user;
    protected $adminFlagChecked;
    public $gmt;
    public $errorlog;

    protected function create()
    {
        parent::create();
        $this->basename = 'options';
        $this->addevents('changed', 'perpagechanged');
        unset($this->cache);
        $this->gmt = 0;
        $this->errorlog = '';
        $this->group = '';
        $this->idgroups = array();
        $this->addmap('groupnames', array());
        $this->addmap('parentgroups', array());
    }

    public function afterLoad()
    {
        parent::afterload();
        date_default_timezone_set($this->timezone);
        $this->gmt = date('Z');
        if (!defined('dbversion')) define('dbversion', true);
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->eventnames)) {
            $this->addevent($name, $value['class'], $value['func']);
            return true;
        }

        if (method_exists($this, $set = 'set' . $name)) {
            $this->$set($value);
            return true;
        }

        if (!array_key_exists($name, $this->data) || ($this->data[$name] != $value)) {
            $this->data[$name] = $value;
            if ($name == 'solt') $this->data['emptyhash'] = $this->hash('');
            $this->save();
            $this->dochanged($name, $value);
        }
        return true;
    }

    private function doChanged($name, $value)
    {
        if ($name == 'perpage') {
            $this->perpagechanged();
            $this->getApp()->cache->clear();
        } elseif ($name == 'cache') {
            $this->getApp()->cache->clear();
        } else {
            $this->changed($name, $value);
        }
    }

    public function delete($name)
    {
        if (array_key_exists($name, $this->data)) {
            unset($this->data[$name]);
            $this->save();
        }
    }

    public function getAdminFlag()
    {
        if (is_null($this->adminFlagChecked)) {
            return $this->adminFlagChecked = $this->authenabled && isset($_COOKIE['litepubl_user_flag']) && ($_COOKIE['litepubl_user_flag'] == 'true');
        }

        return $this->adminFlagChecked;
    }

    public function setAdminFlag($val)
    {
        $this->adminFlagChecked = $val;
    }

    public function getuser()
    {
        if (is_null($this->_user)) {
            $this->_user = $this->authenabled ? $this->authcookie() : false;
        }

        return $this->_user;
    }

    public function setuser($id)
    {
        $this->_user = $id;
    }

    public function authCookie()
    {
        return $this->authcookies(isset($_COOKIE['litepubl_user_id']) ? (int)$_COOKIE['litepubl_user_id'] : 0, isset($_COOKIE['litepubl_user']) ? (string)$_COOKIE['litepubl_user'] : '');
    }

    public function authCookies($iduser, $password)
    {
        if (!$iduser || !$password) return false;
        $password = $this->hash($password);
        if ($password == $this->emptyhash) return false;
        if (!$this->finduser($iduser, $password)) return false;

        $this->_user = $iduser;
        $this->updategroup();
        return $iduser;
    }

    public function findUser($iduser, $cookie)
    {
        if ($iduser == 1) return $this->compare_cookie($cookie);
        if (!$this->usersenabled) return false;

        $users = Users::i();
        try {
            $item = $users->getitem($iduser);
        }
        catch(\Exception $e) {
            return false;
        }

        if ('hold' == $item['status']) return false;
        return ($cookie == $item['cookie']) && (strtotime($item['expired']) > time());
    }

    private function compare_cookie($cookie)
    {
        return !empty($this->cookiehash) && ($this->cookiehash == $cookie) && ($this->cookieexpired > time());
    }

    public function emailExists($email)
    {
        if (!$email) return false;
        if (!$this->authenabled) return false;
        if ($email == $this->email) return 1;
        if (!$this->usersenabled) return false;
        return Users::i()->emailexists($email);
    }

    public function auth($email, $password)
    {
        if (!$this->authenabled) return false;
        if (!$email && !$password) return $this->authcookie();
        return $this->authpassword($this->emailexists($email) , $password);
    }

    public function authPassword($iduser, $password)
    {
        if (!$iduser) return false;
        if ($iduser == 1) {
            if ($this->data['password'] != $this->hash($password)) return false;
        } else {
            if (!Users::i()->authpassword($iduser, $password)) return false;
        }

        $this->_user = $iduser;
        $this->updategroup();
        return $iduser;
    }

    public function updateGroup()
    {
        if ($this->_user == 1) {
            $this->group = 'admin';
            $this->idgroups = array(
                1
            );
        } else {
            $user = Users::i()->getitem($this->_user);
            $this->idgroups = $user['idgroups'];
            $this->group = count($this->idgroups) ? UserGroups::i()->items[$this->idgroups[0]]['name'] : '';
        }
    }

    public function can_edit($idauthor)
    {
        return ($idauthor == $this->user) || ($this->group == 'admin') || ($this->group == 'editor');
    }

    public function getpassword()
    {
        if ($this->user <= 1) {
            return $this->data['password'];
        }

        $users = Users::i();
        return $users->getvalue($this->user, 'password');
    }

    public function changePassword($newpassword)
    {
        $this->data['password'] = $this->hash($newpassword);
        $this->save();
    }

    public function getDBPassword()
    {
        if (function_exists('mcrypt_encrypt')) {
            return static ::decrypt($this->data['dbconfig']['password'], $this->solt . Config::$secret);
        } else {
            return str_rot13(base64_decode($this->data['dbconfig']['password']));
        }
    }

    public function setDBPassword($password)
    {
        if (function_exists('mcrypt_encrypt')) {
            $this->data['dbconfig']['password'] = static ::encrypt($password, $this->solt . Config::$secret);
        } else {
            $this->data['dbconfig']['password'] = base64_encode(str_rot13($password));
        }

        $this->save();
    }

    public function logout()
    {
        $this->setcookies('', 0);
    }

    public function setcookie($name, $value, $expired)
    {
        setcookie($name, $value, $expired, $this->getApp()->site->subdir . '/', false, '', $this->securecookie);
    }

    public function setcookies($cookie, $expired)
    {
        $this->setcookie('litepubl_user_id', $cookie ? $this->_user : '', $expired);
        $this->setcookie('litepubl_user', $cookie, $expired);
        $this->setcookie('litepubl_user_flag', $cookie && ('admin' == $this->group) ? 'true' : '', $expired);

        if ($this->_user == 1) {
            $this->save_cookie($cookie, $expired);
        } else if ($this->_user) {
            Users::i()->setcookie($this->_user, $cookie, $expired);
        }
    }

    public function Getinstalled()
    {
        return isset($this->data['email']);
    }

    public function settimezone($value)
    {
        if (!isset($this->data['timezone']) || ($this->timezone != $value)) {
            $this->data['timezone'] = $value;
            $this->save();
            date_default_timezone_set($this->timezone);
            $this->gmt = date('Z');
        }
    }

    public function save_cookie($cookie, $expired)
    {
        $this->data['cookiehash'] = $cookie ? $this->hash($cookie) : '';
        $this->cookieexpired = $expired;
        $this->save();
    }

    public function hash($s)
    {
        return Str::basemd5((string)$s . $this->solt . Config::$secret);
    }

    public function inGroup($groupname)
    {
        //admin has all rights
        if ($this->user == 1) return true;
        if (in_array($this->groupnames['admin'], $this->idgroups)) return true;
        if (!$groupname) return true;
        $groupname = trim($groupname);
        if ($groupname == 'admin') return false;
        if (!isset($this->groupnames[$groupname])) $this->error(sprintf('The "%s" group not found', $groupname));
        $idgroup = $this->groupnames[$groupname];
        return in_array($idgroup, $this->idgroups);
    }

    public function inGroups(array $idgroups)
    {
        if ($this->ingroup('admin')) return true;
        return count(array_intersect($this->idgroups, $idgroups));
    }

    public function hasGroup($groupname)
    {
        if ($this->ingroup($groupname)) return true;
        // if group is children of user groups
        $idgroup = $this->groupnames[$groupname];
        if (!isset($this->parentgroups[$idgroup])) return false;
        return count(array_intersect($this->idgroups, $this->parentgroups[$idgroup]));
    }

}

//Paths.php
namespace litepubl\core;

class Paths
{
    public $home;
    public $lib;
    public $libinclude;
    public $storage;
    public $data;
    public $cache;
    public $backup;
    public $js;
    public $plugins;
    public $themes;
    public $files;

    public function __construct()
    {
        $this->home = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
        $this->lib = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $this->libinclude = $this->lib . 'include' . DIRECTORY_SEPARATOR;
        $this->languages = $this->lib . 'languages' . DIRECTORY_SEPARATOR;
        $this->storage = $this->home . 'storage' . DIRECTORY_SEPARATOR;
        $this->data = $this->storage . 'data' . DIRECTORY_SEPARATOR;
        $this->cache = $this->storage . 'cache' . DIRECTORY_SEPARATOR;
        $this->backup = $this->storage . 'backup' . DIRECTORY_SEPARATOR;
        $this->plugins = $this->home . 'plugins' . DIRECTORY_SEPARATOR;
        $this->themes = $this->home . 'themes' . DIRECTORY_SEPARATOR;
        $this->files = $this->home . 'files' . DIRECTORY_SEPARATOR;
        $this->js = $this->home . 'js' . DIRECTORY_SEPARATOR;
    }
}

//Plugin.php
namespace litepubl\core;

class Plugin extends Events
{

    protected function create()
    {
        parent::create();
        $this->basename = 'plugins/' . strtolower(str_replace('\\', '-', get_class($this)));
    }

    public function addClass($classname, $filename)
    {
        $ns = dirname(get_class($this));
        $reflector = new \ReflectionClass($class);
        $dir = dirname($reflector->getFileName());

        $this->getApp()->classes->add($ns . '\\' . $classname, $dir . '/' . $filename);
    }

}

//Pool.php
namespace litepubl\core;

class Pool extends Data
{
    protected $perpool;
    protected $pool;
    protected $modified;
    protected $ongetitem;

    protected function create()
    {
        parent::create();
        $this->basename = 'poolitems';
        $this->perpool = 20;
        $this->pool = array();
        $this->modified = array();
    }

    public function getItem($id)
    {
        if (isset($this->ongetitem)) {
            return call_user_func_array($this->ongetitem, array(
                $id
            ));
        }

        $this->error('Call abstract method getitem in class' . get_class($this));
    }

    public function getFilename($idpool)
    {
        return $this->basename . '.pool.' . $idpool;
    }

    public function loadpool($idpool)
    {
        if ($data = $this->getApp()->router->cache->get($this->getfilename($idpool))) {
            $this->pool[$idpool] = $data;
        } else {
            $this->pool[$idpool] = array();
        }
    }

    public function savepool($idpool)
    {
        if (!isset($this->modified[$idpool])) {
            $this->getApp()->onClose->on($this, 'saveModified', $idpool);
            $this->modified[$idpool] = true;
        }
    }

    public function savemodified($idpool)
    {
        $this->getApp()->router->cache->set($this->getfilename($idpool) , $this->pool[$idpool]);
    }

    public function getIdpool($id)
    {
        $idpool = (int)floor($id / $this->perpool);
        if (!isset($this->pool[$idpool])) {
            $this->loadpool($idpool);
        }

        return $idpool;
    }

    public function get($id)
    {
        $idpool = $this->getidpool($id);
        if (isset($this->pool[$idpool][$id])) {
            return $this->pool[$idpool][$id];
        }
        $result = $this->getitem($id);
        $this->pool[$idpool][$id] = $result;
        $this->savepool($idpool);
        return $result;
    }

    public function set($id, $item)
    {
        $idpool = $this->getidpool($id);
        $this->pool[$idpool][$id] = $item;
        $this->savepool($idpool);
    }

}

//PoolStorage.php
namespace litepubl\core;

class PoolStorage
{
    use AppTrait;

    public $data;
    private $modified;

    public function __construct()
    {
        $this->data = [];
        $this->loadData();
    }

    public function getStorage()
    {
        return $this->getApp()->storage;
    }

    public function save(Data $obj)
    {
        $this->modified = true;
        $base = $obj->getBaseName();
        if (!isset($this->data[$base])) {
            $this->data[$base] = & $obj->data;
        }

        return true;
    }

    public function load(Data $obj)
    {
        $base = $obj->getbasename();
        if (isset($this->data[$base])) {
            $obj->data = & $this->data[$base];
            return true;
        } else {
            $this->data[$base] = & $obj->data;
            return false;
        }
    }

    public function remove(Data $obj)
    {
        $base = $obj->getbasename();
        if (isset($this->data[$base])) {
            unset($this->data[$base]);
            $this->modified = true;
            return true;
        }
    }

    public function loadData()
    {
        if ($data = $this->getStorage()->loaddata($this->getApp()->paths->data . 'storage')) {
            $this->data = $data;
            return true;
        }

        return false;
    }

    public function commit()
    {
        if (!$this->modified) {
            return false;
        }

        $lockfile = $this->getApp()->paths->data . 'storage.lok';
        if (($fh = @\fopen($lockfile, 'w')) && \flock($fh, LOCK_EX | LOCK_NB)) {
            $this->getStorage()->saveData($this->getApp()->paths->data . 'storage', $this->data);
            $this->modified = false;
            \flock($fh, LOCK_UN);
            \fclose($fh);
            @\chmod($lockfile, 0666);
            return true;
        } else {
            if ($fh) {
                @\fclose($fh);
            }

            $this->error('Storage locked, data not saved');
            return false;
        }
    }

    public function error($mesg)
    {
        $this->getApp()->getLogger()->error($mesg);
    }

    public function isInstalled()
    {
        return count($this->data);
    }

}

//PoolStorageTrait.php
namespace litepubl\core;

trait PoolStorageTrait
{

    public function getStorage()
    {
        return $this->getApp()->poolStorage;
    }

}

//PropException.php
namespace litepubl\core;

class PropException extends \UnexpectedValueException
{
    public $propName;
    public $className;

    public function __construct($className, $propName)
    {
        $this->className = $className;
        $this->propName = $propName;

        parent::__construct(sprintf('The requested property "%s" not found in class  %s', $propName, $className) , 404);
    }
}

//Request.php
namespace litepubl\core;

use litepubl\Config;

class Request
{
    use AppTrait;

    public $host;
    public $isAdminPanel;
    public $page;
    public $url;
    public $uripath;

    public function __construct($host, $url)
    {
        $this->host = $this->getHost($host);
        $this->page = 1;
        $this->uripath = [];

        if ($url) {
            $app = $this->getApp();
            if ($app->site->q == '?') {
                $this->url = substr($url, strlen($app->site->subdir));
            } else {
                $this->url = $_GET['url'];
            }
        } else {
            $this->url = '';
        }

        $this->isAdminPanel = Str::begin($this->url, '/admin/') || ($this->url == '/admin');
    }

    public function getHost($host)
    {
        if (Config::$host) {
            return config::$host;
        }

        $host = \strtolower(\trim($host));
        if ($host && \preg_match('/(www\.)?([\w\.\-]+)(:\d*)?/', $host, $m)) {
            return $m[2];
        }

        return 'localhost';
    }

    public function getInput()
    {
        return file_get_contents('php://input');
    }

    public function getGet()
    {
        return $_GET;
    }

    public function getPost()
    {
        return $_POST;
    }

    public function getArg($name, $default = false)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    public function getNextPage()
    {
        $url = $this->itemRoute['url'];
        return $this->getApp()->site->url . rtrim($url, '/') . '/page/' . ($this->page + 1) . '/';
    }

    public function getPrevpage()
    {
        $url = $this->itemRoute['url'];
        if ($this->page <= 2) {
            return url;
        }

        return $this->getApp()->site->url . rtrim($url, '/') . '/page/' . ($this->page - 1) . '/';
    }

    public function signedRef()
    {
        if (isset($_GET['ref'])) {
            $ref = $_GET['ref'];
            $url = $this->url;
            $url = substr($url, 0, strpos($url, '&ref='));
            $app = $this->getApp();
            if ($ref == md5(Config::$secret . $app->site->url . $url . $app->options->solt)) {
                return true;
            }
        }
    }

    public function isXXX()
    {
        if ($this->signedRef()) {
            return false;
        }

        $host = '';
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $p = parse_url($_SERVER['HTTP_REFERER']);
            $host = $p['host'];
        }

        return $host != $this->host;
    }

    public function checkAttack()
    {
        return $this->getApp()->options->xxxcheck && $this->isXXX();
    }

}

//Response.php
namespace litepubl\core;

class Response
{
    use AppTrait;

    public $body;
    public $cache;
    public $cacheHeader;
    public $headers;
    public $protocol;
    public $status;
    protected $phrases = [200 => 'OK', 206 => 'Partial Content', 301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 307 => 'Temporary Redirect', 400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 500 => 'Internal Server Error', 503 => 'Service Unavailable', ];

    public function __construct()
    {
        $this->body = '';
        $this->cache = true;
        $this->protocol = '1.1';
        $this->status = 200;
        $this->headers = ['Content-type' => 'text/html;charset=utf-8', 'Last-Modified' => date('r') ,
        //'X-Pingback' => $this->getApp()->site->url . '/rpc.xml',
        ];
    }

    public function __set($name, $value)
    {
        if (method_exists($this, $set = 'set' . $name)) {
            $this->$set($value);
        } else {
            throw new PropException(get_class($this) , $name);
        }
    }

    public function setCache($cache)
    {
        $this->cache = $cache;
        $this->cacheHeader = $cache;
    }

    public function setCacheHeaders($mode)
    {
        if ($mode) {
            unset($this->headers['Cache-Control']);
            unset($this->headers['Pragma']);
        } else {
            $this->headers['Cache-Control'] = 'no-cache, must-revalidate';
            $this->headers['Pragma'] = 'no-cache';
        }
    }

    public function send()
    {
        if (!isset($this->phrases[$this->status])) {
            $this->getApp()->getLogger()->warning(sprintf('Phrase for status %s not exists', $this->status));
        }

        header(sprintf('HTTP/%s %s %s', $this->protocol, $this->status, $this->phrases[$this->status]) , true, $this->status);

        $this->setCacheHeaders($this->cacheHeader);
        if (isset($this->headers['Date'])) {
            unset($this->headers['Last-Modified']);
        }

        foreach ($this->headers as $k => $v) {
            header(sprintf('%s: %s', $k, $v));
        }

        if (is_string($this->body)) {
            eval('?>' . $this->body);
            /*
            return;
            $f = $this->getApp()->paths->cache . 'temp.php';
            file_put_contents($f, $this->body);
            require ($f);
            */
        } elseif (is_callable($this->body)) {
            call_user_func_array($this->body, [$this]);
        }
    }

    public function getString()
    {
        return $this->__tostring();
    }

    public function __tostring()
    {
        $headers = sprintf('header(\'HTTP/%s %d %s\', true, %d);', $this->protocol, $this->status, $this->phrases[$this->status], $this->status);

        foreach ($this->headers as $k => $v) {
            $headers.= sprintf('header(\'%s: %s\');', $k, $v);
        }

        $result = sprintf('<?php %s ?>', $headers);
        if ($this->body) {
            $result.= $this->body;
        }

        return $result;
    }

    public function setXml()
    {
        $this->headers['Content-Type'] = 'text/xml; charset=utf-8';
        $this->body.= '<?php echo \'<?xml version="1.0" encoding="utf-8" ?>\'; ?>';
    }

    public function setJson($js = '')
    {
        $this->headers['Content-Type'] = 'application/json;charset=utf-8';
        if ($js) {
            $this->cache = false;
            $this->body = $js;
            $this->headers['Connection'] = 'close';
            $this->headers['Content-Length'] = strlen($js);
            $this->headers['Date'] = date('r');
        }
    }

    public function redir($url, $status = 301)
    {
        $this->status = $status;

        //check if relative path
        if (!strpos($url, '://')) {
            $url = $this->getApp()->site->url . $url;
        }

        $this->headers['Location'] = $url;
    }

    public function isRedir()
    {
        return in_array($this->status, [301, 302, 303, 307]);
    }

    public function forbidden()
    {
        $this->status = 403;
        $this->cache = false;
    }

    public function closeConnection()
    {
        $len = ob_get_length();
        header('Connection: close');
        header('Content-Length: ' . $len);
        header('Content-Encoding: none');
    }

    public function getReasonPhrase()
    {
        return $this->phrases[$this->status];
    }

}

//ResponsiveInterface.php
namespace litepubl\core;

interface ResponsiveInterface {
    public function request(Context $context);
}

//Router.php
namespace litepubl\core;

use litepubl\pages\Redirector;

class Router extends Items
{
    public $prefilter;

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->table = 'urlmap';
        $this->basename = 'urlmap';
        $this->addevents('beforerequest', 'afterrequest', 'onclearcache');
        $this->data['disabledcron'] = false;
        $this->data['redirdom'] = false;
        $this->addmap('prefilter', array());
    }

    public function request(Context $context)
    {
        $app = $this->getApp();
        if ($this->redirdom && $app->site->fixedurl) {
            $parsedUrl = parse_url($app->site->url . '/');
            if ($context->request->host != strtolower($parsedUrl['host'])) {
                $context->response->redir($app->site->url . $context->request->url);
                return;
            }
        }

        $this->beforerequest($context);
        $context->itemRoute = $this->queryItem($context);
    }

    public function queryItem(Context $context)
    {
        $url = $context->request->url;
        if ($result = $this->query($url)) {
            return $result;
        }

        $srcurl = $url;
        $response = $context->response;

        if ($i = strpos($url, '?')) {
            $url = substr($url, 0, $i);
        }

        if ('//' == substr($url, -2)) {
            $response->redir(rtrim($url, '/') . '/');
            return false;
        }

        //extract page number
        if (preg_match('/(.*?)\/page\/(\d*?)\/?$/', $url, $m)) {
            if ('/' != substr($url, -1)) {
                $response->redir($url . '/');
                return false;
            }

            $url = $m[1];
            if (!$url) {
                $url = '/';
            }

            $context->request->page = max(1, abs((int)$m[2]));
        }

        if (($srcurl != $url) && ($result = $this->query($url))) {
            if (($context->request->page == 1) && ($result['type'] == 'normal') && ($srcurl != $result['url'])) {
                $response->redir($result['url']);
            }

            return $result;
        }

        $url = $url != rtrim($url, '/') ? rtrim($url, '/') : $url . '/';
        if (($srcurl != $url) && ($result = $this->query($url))) {
            if (($this->page == 1) && ($result['type'] == 'normal') && ($srcurl != $result['url'])) {
                $response->redir($result['url']);
            }

            return $result;
        }

        $context->request->uripath = explode('/', trim($url, '/'));
        return false;
    }

    public function getIdurl($id)
    {
        if (!isset($this->items[$id])) {
            $this->items[$id] = $this->db->getitem($id);
        }
        return $this->items[$id]['url'];
    }

    public function findUrl($url)
    {
        return $this->db->findItem('url = ' . Str::quote($url));
    }

    public function urlExists($url)
    {
        return $this->db->findid('url = ' . Str::quote($url));
    }

    private function query($url)
    {
        if ($item = $this->findfilter($url)) {
            $this->items[$item['id']] = $item;
            return $item;
        } else if ($item = $this->db->getassoc('url = ' . Str::quote($url) . ' limit 1')) {
            $this->items[$item['id']] = $item;
            return $item;
        }

        return false;
    }

    public function findFilter($url)
    {
        foreach ($this->prefilter as $item) {
            switch ($item['type']) {
                case 'begin':
                    if (Str::begin($url, $item['url'])) {
                        return $item;
                    }
                    break;


                case 'end':
                    if (Str::end($url, $item['url'])) {
                        return $item;
                    }
                    break;


                case 'regexp':
                    if (preg_match($item['url'], $url)) {
                        return $item;
                    }
                    break;
            }
        }

        return false;
    }

    public function updateFilter()
    {
        $this->prefilter = $this->db->getitems('type in (\'begin\', \'end\', \'regexp\')');
        $this->save();
    }

    public function addGet($url, $class)
    {
        return $this->add($url, $class, null, 'get');
    }

    public function add($url, $class, $arg, $type = 'normal')
    {
        if (empty($url)) {
            $this->error('Empty url to add');
        }

        if (empty($class)) {
            $this->error('Empty class name of adding url');
        }

        if (!in_array($type, array(
            'normal',
            'get',
            'usernormal',
            'userget',
            'begin',
            'end',
            'regexp'
        ))) {
            $this->error(sprintf('Invalid url type %s', $type));
        }

        if ($item = $this->db->findItem('url = ' . Str::quote($url))) {
            $this->error(sprintf('Url "%s" already exists', $url));
        }

        $item = array(
            'url' => $url,
            'class' => $class,
            'arg' => (string)$arg,
            'type' => $type
        );

        $item['id'] = $this->db->add($item);
        $this->items[$item['id']] = $item;

        if (in_array($type, array(
            'begin',
            'end',
            'regexp'
        ))) {
            $this->prefilter[] = $item;
            $this->save();
        }

        return $item['id'];
    }

    public function delete($url)
    {
        $url = Str::quote($url);
        if ($id = $this->db->findid('url = ' . $url)) {
            $this->db->iddelete($id);
        } else {
            return false;
        }

        foreach ($this->prefilter as $i => $item) {
            if ($id == $item['id']) {
                unset($this->prefilter[$i]);
                $this->save();
                break;
            }
        }

        $this->clearcache();
        $this->deleted($id);
        return true;
    }

    public function deleteClass($class)
    {
        if ($items = $this->db->getItems('class = ' . Str::quote($class))) {
            foreach ($items as $item) {
                $this->db->idDelete($item['id']);
                $this->deleted($item['id']);
            }
        }

        $this->clearcache();
    }

    public function deleteItem($id)
    {
        if ($item = $this->db->getitem($id)) {
            $this->db->idDelete($id);
            $this->deleted($id);
        }

        $this->clearcache();
    }

    //for Archives
    public function getUrlsOfClass($class)
    {
        $res = $this->db->query("select url from $this->thistable where class = " . Str::quote($class));
        return $this->db->res2id($res);
    }
    public function addRedir($from, $to)
    {
        if ($from == $to) {
            return;
        }

        $Redir = Redirector::i();
        $Redir->add($from, $to);
    }

    public static function unsub($obj)
    {
        static ::i()->unbind($obj);
    }

    public function unbind($obj)
    {
        $this->lock();
        parent::unbind($obj);
        $this->deleteClass(get_class($obj));
        $this->updateFilter();
        $this->unlock();
    }

    public function setUrlValue($url, $name, $value)
    {
        if ($id = $this->urlExists($url)) {
            $this->setValue($id, $name, $value);
        }
    }

    public function setIdUrl($id, $url)
    {
        $this->db->setValue($id, 'url', $url);
        if (isset($this->items[$id])) {
            $this->items[$id]['url'] = $url;
        }
    }

    //backward compabilty
    public function clearCache()
    {
        $this->getApp()->cache->clear();
    }

}

//Singleton.php
namespace litepubl\core;

trait Singleton
{

    public static function i()
    {
        return litepubl::$app->classes->getInstance(get_called_class());
    }
}

//Site.php
namespace litepubl\core;

use litepubl\Config;

class Site extends Events
{
    use PoolStorageTrait;

    public $mapoptions;
    private $users;

    protected function create()
    {
        parent::create();
        $this->basename = 'site';
        $this->addmap('mapoptions', array(
            'version' => 'version',
            'language' => 'language',
        ));
    }

    public function __get($name)
    {
        if (isset($this->mapoptions[$name])) {
            $prop = $this->mapoptions[$name];
            if (is_array($prop)) {
                list($classname, $method) = $prop;
                return call_user_func_array(array(
                    static ::iGet($classname) ,
                    $method
                ) , array(
                    $name
                ));
            }

            return $this->getApp()->options->data[$prop];
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if ($name == 'url') {
            return $this->seturl($value);
        }

        if (in_array($name, $this->eventnames)) {
            $this->addevent($name, $value['class'], $value['func']);
        } elseif (isset($this->mapoptions[$name])) {
            $prop = $this->mapoptions[$name];
            if (is_string($prop)) $this->getApp()->options->{$prop} = $value;
        } elseif (!array_key_exists($name, $this->data) || ($this->data[$name] != $value)) {
            $this->data[$name] = $value;
            $this->save();
        }
        return true;
    }

    public function getUrl()
    {
        if ($this->fixedurl) {
            return $this->data['url'];
        }

        return 'http://' . $this->getApp()->context->request->host;
    }

    public function getFiles()
    {
        if ($this->fixedurl) {
            return $this->data['files'];
        }

        return 'http://' . $this->getApp()->context->request->host;
    }

    public function setUrl($url)
    {
        $url = rtrim($url, '/');
        $this->data['url'] = $url;
        $this->data['files'] = $url;
        $this->subdir = '';
        if ($i = strpos($url, '/', 10)) {
            $this->subdir = substr($url, $i);
        }
        $this->save();
    }

    public function getDomain()
    {
        if (Config::$host) {
            return Config::$host;
        } else {
            $url = $this->url;
            return substr($url, strpos($url, '//') + 2);
        }
    }

    public function getUserlink()
    {
        if ($id = $this->getApp()->options->user) {
            if (!isset($this->users)) $this->users = array();
            if (isset($this->users[$id])) {
                return $this->users[$id];
            }

            $item = Users::i()->getitem($id);
            if ($item['website']) {
                $result = sprintf('<a href="%s">%s</a>', $item['website'], $item['name']);
            } else {
                $page = $this->getdb('userpage')->getitem($id);
                if ((int)$page['idurl']) {
                    $result = sprintf('<a href="%s%s">%s</a>', $this->url, $this->getApp()->router->getvalue($page['idurl'], 'url') , $item['name']);
                } else {
                    $result = $item['name'];
                }
            }
            $this->users[$id] = $result;
            return $result;
        }
        return '';
    }

    public function getLiveuser()
    {
        return '<?php echo  litepubl::$app->site->getuserlink(); ?>';
    }

}

//Storage.php
namespace litepubl\core;

class Storage
{
    use AppTrait;

    public function getExt()
    {
        return '.php';
    }

    public function serialize(array $data)
    {
        return \serialize($data);
    }

    public function unserialize($str)
    {
        if ($str) {
            return \unserialize($str);
        }

        return false;
    }

    public function before($str)
    {
        return \sprintf('<?php /* %s */ ?>', \str_replace('*/', '**//*/', $str));
    }

    public function after($str)
    {
        return \str_replace('**//*/', '*/', \substr($str, 9, \strlen($str) - 9 - 6));
    }

    public function getFilename(Data $obj)
    {
        return $this->getApp()->paths->data . $obj->getbasename();
    }

    public function save(Data $obj)
    {
        return $this->saveFile($this->getfilename($obj) , $this->serialize($obj->data));
    }

    public function saveData($filename, array $data)
    {
        return $this->saveFile($filename, $this->serialize($data));
    }

    public function load(Data $obj)
    {
        try {
            if ($data = $this->loadData($this->getfilename($obj))) {
                $obj->data = $data + $obj->data;
                return true;
            }
        }
        catch(\Exception $e) {
            $this->getApp()->logException($e);
        }

        return false;
    }

    public function loadData($filename)
    {
        if ($s = $this->loadFile($filename)) {
            return $this->unserialize($s);
        }

        return false;
    }

    public function loadFile($filename)
    {
        if (\file_exists($filename . $this->getExt()) && ($s = \file_get_contents($filename . $this->getExt()))) {
            return $this->after($s);
        }

        return false;
    }

    public function saveFile($filename, $content)
    {
        $tmp = $filename . '.tmp' . $this->getExt();
        if (false === \file_put_contents($tmp, $this->before($content))) {
            $this->error(\sprintf('Error write to file "%s"', $tmp));
            return false;
        }

        \chmod($tmp, 0666);

        //replace file
        $curfile = $filename . $this->getExt();
        if (\file_exists($curfile)) {
            $backfile = $filename . '.bak' . $this->getExt();
            $this->delete($backfile);
            \rename($curfile, $backfile);
        }

        if (!\rename($tmp, $curfile)) {
            $this->error(sprintf('Error rename temp file "%s" to "%s"', $tmp, $curfile));
            return false;
        }

        return true;
    }

    public function remove($filename)
    {
        $this->delete($filename . $this->getExt());
        $this->delete($filename . '.bak' . $this->getExt());
    }

    public function delete($filename)
    {
        if (\file_exists($filename) && !\unlink($filename)) {
            \chmod($filename, 0666);
            \unlink($filename);
        }
    }

    public function error($mesg)
    {
        $this->getApp()->options->trace($mesg);
    }

}

//StorageInc.php
namespace litepubl\core;

class StorageInc extends Storage
{

    public function getExt()
    {
        return '.inc.php';
    }

    public function serialize(array $data)
    {
        return \var_export($data, true);
    }

    public function unserialize($str)
    {
        $this->error('Call unserialize');
    }

    public function before($str)
    {
        return \sprintf('<?php return %s;', $str);
    }

    public function after($str)
    {
        $this->error('Call after method');
    }

    public function loadData($filename)
    {
        if (\file_exists($filename . $this->getExt())) {
            return include ($filename . $this->getExt());
        }

        return false;
    }

    public function loadFile($filename)
    {
        $this->error('Call loadfile');
    }

}

//StorageMemcache.php
namespace litepubl\core;

class StorageMemcache extends Storage
{
    public $memcache;

    public function __construct()
    {
        parent::__construct();
        $this->memcache = $this->getApp()->memcache;
    }

    public function loadFile($filename)
    {
        if ($s = $this->memcache->get($filename)) {
            return $s;
        }

        if ($s = parent::loadFile($filename)) {
            $this->memcache->set($filename, $s, false, 3600);
            return $s;
        }

        return false;
    }

    public function saveFile($filename, $content)
    {
        $this->memcache->set($filename, $content, false, 3600);
        return parent::saveFile($filename, $content);
    }

    public function delete($filename)
    {
        parent::delete($filename);
        $this->memcache->delete($filename);
    }

}

//Str.php
namespace litepubl\core;

use litepubl\Config;

Class Str
{
    public $value;

    public function __construct($s = '')
    {
        $this->value = (string)$s;
    }

    public function __tostring()
    {
        return $this->value;
    }

    //static methods
    public static function sqlDate($date = 0)
    {
        if (!$date) {
            $date = time();
        }

        return date('Y-m-d H:i:s', $date);
    }

    public static function sqlTime($date = 0)
    {
        if ($date) {
            return date('Y-m-d H:i:s', $date);
        }

        return '0000-00-00 00:00:00';
    }

    public static function quote($s)
    {
        return litepubl::$app->db->quote($s);
    }

    public static function md5Rand()
    {
        return md5(mt_rand() . Config::$secret . microtime());
    }

    public static function md5Uniq()
    {
        return static ::baseMd5(mt_rand() . Config::$secret . microtime());
    }

    public static function baseMd5($s)
    {
        return trim(base64_encode(md5($s, true)) , '=');
    }

    public static function begin($s, $begin)
    {
        return strncmp($s, $begin, strlen($begin)) == 0;
    }

    public static function begins()
    {
        $a = func_get_args();
        $s = array_shift($a);
        while ($begin = array_shift($a)) {
            if (strncmp($s, $begin, strlen($begin)) == 0) {
                return true;
            }

        }
        return false;
    }

    public static function end($s, $end)
    {
        return $end == substr($s, 0 - strlen($end));
    }

    public static function trimUtf($s)
    {
        $utf = "\xEF\xBB\xBF";
        return static ::begin($s, $utf) ? substr($s, strlen($utf)) : $s;
    }

    public static function toArray($s)
    {
        $a = explode("\n", trim($s));
        foreach ($a as $k => $v) {
            $a[$k] = trim($v);
        }

        return $a;
    }

    public static function toIntArray($s)
    {
        $result = array();
        foreach (explode(',', $s) as $value) {
            if ($v = (int)trim($value)) {
                $result[] = $v;
            }
        }

        return $result;
    }

    public static function toJson($a)
    {
        return json_encode($a, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    public static function jsonAttr($a)
    {
        return str_replace('"', '&quot;', Str::toJson($a));
    }

    public static function log($s)
    {
        echo "<pre>\n", htmlspecialchars($s) , "</pre>\n";
    }

    public static function dump($v)
    {
        echo "<pre>\n";
        var_dump($v);
        echo "</pre>\n";
    }

}

//TempProps.php
namespace litepubl\core;

class TempProps extends \ArrayObject
{
    private $owner;

    public function __construct(Data $owner)
    {
        parent::__construct([], \ArrayObject::ARRAY_AS_PROPS);
        $this->owner = $owner;
        $owner->coinstances[] = $this;
    }

    public function __destruct()
    {
        foreach ($this->owner->coinstances as $i => $obj) {
            if ($this == $obj) {
                unset($this->owner->coinstances[$i]);
            }
        }

        $this->owner = null;
    }

}

//Users.php
namespace litepubl\core;

class Users extends Items
{
    public $grouptable;

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'users';
        $this->table = 'users';
        $this->grouptable = 'usergroup';
        $this->addevents('beforedelete');
    }

    public function res2items($res)
    {
        if (!$res) {
            return array();
        }

        $result = array();
        $db = $this->getApp()->db;
        while ($item = $db->fetchassoc($res)) {
            $id = (int)$item['id'];
            $item['idgroups'] = Str::toIntArray($item['idgroups']);
            $result[] = $id;
            $this->items[$id] = $item;
        }
        return $result;
    }

    public function add(array $values)
    {
        return Usersman::i()->add($values);
    }

    public function edit($id, array $values)
    {
        return Usersman::i()->edit($id, $values);
    }

    public function setGroups($id, array $idgroups)
    {
        $idgroups = array_unique($idgroups);
        Arr::deleteValue($idgroups, '');
        Arr::deleteValue($idgroups, false);
        Arr::deleteValue($idgroups, null);

        $this->items[$id]['idgroups'] = $idgroups;
        $db = $this->getdb($this->grouptable);
        $db->delete("iduser = $id");
        foreach ($idgroups as $idgroup) {
            $db->add(array(
                'iduser' => $id,
                'idgroup' => $idgroup
            ));
        }
    }

    public function delete($id)
    {
        if ($id == 1) {
            return;
        }

        $this->beforedelete($id);
        $this->getdb($this->grouptable)->delete('iduser = ' . (int)$id);
        $this->pages->delete($id);
        $this->getdb('comments')->update("status = 'deleted'", "author = $id");
        return parent::delete($id);
    }

    public function getPages()
    {
        return \litepubl\pages\Users::i();
    }

    public function emailexists($email)
    {
        if ($email == '') {
            return false;
        }

        if ($email == $this->getApp()->options->email) {
            return 1;
        }

        foreach ($this->items as $id => $item) {
            if ($email == $item['email']) {
                return $id;
            }

        }

        if ($item = $this->db->finditem('email = ' . Str::quote($email))) {
            $id = (int)$item['id'];
            $this->items[$id] = $item;
            return $id;
        }

        return false;
    }

    public function getPassword($id)
    {
        return $id == 1 ? $this->getApp()->options->password : $this->getvalue($id, 'password');
    }

    public function changepassword($id, $password)
    {
        $item = $this->getitem($id);
        $this->setvalue($id, 'password', $this->getApp()->options->hash($item['email'] . $password));
    }

    public function approve($id)
    {
        $this->setvalue($id, 'status', 'approved');
        $pages = $this->pages;
        if ($pages->createpage) {
            $pages->addpage($id);
        }
    }

    public function auth($email, $password)
    {
        return $this->authpassword($this->emailexists($email) , $password);
    }

    public function authpassword($id, $password)
    {
        if (!$id || !$password) {
            return false;
        }

        $item = $this->getitem($id);
        if ($item['password'] == $this->getApp()->options->hash($item['email'] . $password)) {
            if ($item['status'] == 'wait') $this->approve($id);
            return $id;
        }
        return false;
    }

    public function authcookie($cookie)
    {
        $cookie = (string)$cookie;
        if (empty($cookie)) {
            return false;
        }

        $cookie = $this->getApp()->options->hash($cookie);
        if ($cookie == $this->getApp()->options->hash('')) {
            return false;
        }

        if ($id = $this->findcookie($cookie)) {
            $item = $this->getitem($id);
            if (strtotime($item['expired']) > time()) {
                return $id;
            }

        }
        return false;
    }

    public function findcookie($cookie)
    {
        $cookie = Str::quote($cookie);
        if (($a = $this->select('cookie = ' . $cookie, 'limit 1')) && (count($a) > 0)) {
            return (int)$a[0];
        }
        return false;
    }

    public function getGroupname($id)
    {
        $item = $this->getitem($id);
        $groups = UserGroups::i();
        return $groups->items[$item['idgroups'][0]]['name'];
    }

    public function clearcookie($id)
    {
        $this->setcookie($id, '', 0);
    }

    public function setCookie($id, $cookie, $expired)
    {
        if ($cookie) $cookie = $this->getApp()->options->hash($cookie);
        $expired = Str::sqlDate($expired);
        if (isset($this->items[$id])) {
            $this->items[$id]['cookie'] = $cookie;
            $this->items[$id]['expired'] = $expired;
        }

        $this->db->updateassoc(array(
            'id' => $id,
            'cookie' => $cookie,
            'expired' => $expired
        ));
    }

}

//vendor/monolog/monolog/src/Monolog/Handler/AbstractHandler.php
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Logger;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;

/**
 * Base Handler class providing the Handler structure
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
abstract class AbstractHandler implements HandlerInterface
{
    protected $level = Logger::DEBUG;
    protected $bubble = true;

    /**
     * @var FormatterInterface
     */
    protected $formatter;
    protected $processors = array();

    /**
     * @param int     $level  The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        $this->setLevel($level);
        $this->bubble = $bubble;
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return $record['level'] >= $this->level;
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        foreach ($records as $record) {
            $this->handle($record);
        }
    }

    /**
     * Closes the handler.
     *
     * This will be called automatically when the object is destroyed
     */
    public function close()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function pushProcessor($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method), '.var_export($callback, true).' given');
        }
        array_unshift($this->processors, $callback);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function popProcessor()
    {
        if (!$this->processors) {
            throw new \LogicException('You tried to pop from an empty processor stack.');
        }

        return array_shift($this->processors);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        if (!$this->formatter) {
            $this->formatter = $this->getDefaultFormatter();
        }

        return $this->formatter;
    }

    /**
     * Sets minimum logging level at which this handler will be triggered.
     *
     * @param  int|string $level Level or level name
     * @return self
     */
    public function setLevel($level)
    {
        $this->level = Logger::toMonologLevel($level);

        return $this;
    }

    /**
     * Gets minimum logging level at which this handler will be triggered.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Sets the bubbling behavior.
     *
     * @param  Boolean $bubble true means that this handler allows bubbling.
     *                         false means that bubbling is not permitted.
     * @return self
     */
    public function setBubble($bubble)
    {
        $this->bubble = $bubble;

        return $this;
    }

    /**
     * Gets the bubbling behavior.
     *
     * @return Boolean true means that this handler allows bubbling.
     *                 false means that bubbling is not permitted.
     */
    public function getBubble()
    {
        return $this->bubble;
    }

    public function __destruct()
    {
        try {
            $this->close();
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * Gets the default formatter.
     *
     * @return FormatterInterface
     */
    protected function getDefaultFormatter()
    {
        return new LineFormatter();
    }
}

//vendor/monolog/monolog/src/Monolog/Handler/AbstractProcessingHandler.php
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

/**
 * Base Handler class providing the Handler structure
 *
 * Classes extending it should (in most cases) only implement write($record)
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Christophe Coevoet <stof@notk.org>
 */
abstract class AbstractProcessingHandler extends AbstractHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $record = $this->processRecord($record);

        $record['formatted'] = $this->getFormatter()->format($record);

        $this->write($record);

        return false === $this->bubble;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    abstract protected function write(array $record);

    /**
     * Processes a record.
     *
     * @param  array $record
     * @return array
     */
    protected function processRecord(array $record)
    {
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }

        return $record;
    }
}

//vendor/monolog/monolog/src/Monolog/Logger.php
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\InvalidArgumentException;

/**
 * Monolog log channel
 *
 * It contains a stack of Handlers and a stack of Processors,
 * and uses them to store records that are added to it.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Logger implements LoggerInterface
{
    /**
     * Detailed debug information
     */
    const DEBUG = 100;

    /**
     * Interesting events
     *
     * Examples: User logs in, SQL logs.
     */
    const INFO = 200;

    /**
     * Uncommon events
     */
    const NOTICE = 250;

    /**
     * Exceptional occurrences that are not errors
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    const WARNING = 300;

    /**
     * Runtime errors
     */
    const ERROR = 400;

    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    const CRITICAL = 500;

    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    const ALERT = 550;

    /**
     * Urgent alert.
     */
    const EMERGENCY = 600;

    /**
     * Monolog API version
     *
     * This is only bumped when API breaks are done and should
     * follow the major version of the library
     *
     * @var int
     */
    const API = 1;

    /**
     * Logging levels from syslog protocol defined in RFC 5424
     *
     * @var array $levels Logging levels
     */
    protected static $levels = array(
        self::DEBUG     => 'DEBUG',
        self::INFO      => 'INFO',
        self::NOTICE    => 'NOTICE',
        self::WARNING   => 'WARNING',
        self::ERROR     => 'ERROR',
        self::CRITICAL  => 'CRITICAL',
        self::ALERT     => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    );

    /**
     * @var \DateTimeZone
     */
    protected static $timezone;

    /**
     * @var string
     */
    protected $name;

    /**
     * The handler stack
     *
     * @var HandlerInterface[]
     */
    protected $handlers;

    /**
     * Processors that will process all log records
     *
     * To process records of a single handler instead, add the processor on that specific handler
     *
     * @var callable[]
     */
    protected $processors;

    /**
     * @var bool
     */
    protected $microsecondTimestamps = true;

    /**
     * @param string             $name       The logging channel
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[]         $processors Optional array of processors
     */
    public function __construct($name, array $handlers = array(), array $processors = array())
    {
        $this->name = $name;
        $this->handlers = $handlers;
        $this->processors = $processors;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return a new cloned instance with the name changed
     *
     * @return static
     */
    public function withName($name)
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }

    /**
     * Pushes a handler on to the stack.
     *
     * @param  HandlerInterface $handler
     * @return $this
     */
    public function pushHandler(HandlerInterface $handler)
    {
        array_unshift($this->handlers, $handler);

        return $this;
    }

    /**
     * Pops a handler from the stack
     *
     * @return HandlerInterface
     */
    public function popHandler()
    {
        if (!$this->handlers) {
            throw new \LogicException('You tried to pop from an empty handler stack.');
        }

        return array_shift($this->handlers);
    }

    /**
     * Set handlers, replacing all existing ones.
     *
     * If a map is passed, keys will be ignored.
     *
     * @param  HandlerInterface[] $handlers
     * @return $this
     */
    public function setHandlers(array $handlers)
    {
        $this->handlers = array();
        foreach (array_reverse($handlers) as $handler) {
            $this->pushHandler($handler);
        }

        return $this;
    }

    /**
     * @return HandlerInterface[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * Adds a processor on to the stack.
     *
     * @param  callable $callback
     * @return $this
     */
    public function pushProcessor($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method), '.var_export($callback, true).' given');
        }
        array_unshift($this->processors, $callback);

        return $this;
    }

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable
     */
    public function popProcessor()
    {
        if (!$this->processors) {
            throw new \LogicException('You tried to pop from an empty processor stack.');
        }

        return array_shift($this->processors);
    }

    /**
     * @return callable[]
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * Control the use of microsecond resolution timestamps in the 'datetime'
     * member of new records.
     *
     * Generating microsecond resolution timestamps by calling
     * microtime(true), formatting the result via sprintf() and then parsing
     * the resulting string via \DateTime::createFromFormat() can incur
     * a measurable runtime overhead vs simple usage of DateTime to capture
     * a second resolution timestamp in systems which generate a large number
     * of log events.
     *
     * @param bool $micro True to use microtime() to create timestamps
     */
    public function useMicrosecondTimestamps($micro)
    {
        $this->microsecondTimestamps = (bool) $micro;
    }

    /**
     * Adds a log record.
     *
     * @param  int     $level   The logging level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = array())
    {
        if (!$this->handlers) {
            $this->pushHandler(new StreamHandler('php://stderr', static::DEBUG));
        }

        $levelName = static::getLevelName($level);

        // check if any handler will handle this message so we can return early and save cycles
        $handlerKey = null;
        reset($this->handlers);
        while ($handler = current($this->handlers)) {
            if ($handler->isHandling(array('level' => $level))) {
                $handlerKey = key($this->handlers);
                break;
            }

            next($this->handlers);
        }

        if (null === $handlerKey) {
            return false;
        }

        if (!static::$timezone) {
            static::$timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
        }

        if ($this->microsecondTimestamps) {
            $ts = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), static::$timezone);
        } else {
            $ts = new \DateTime(null, static::$timezone);
        }
        $ts->setTimezone(static::$timezone);

        $record = array(
            'message' => (string) $message,
            'context' => $context,
            'level' => $level,
            'level_name' => $levelName,
            'channel' => $this->name,
            'datetime' => $ts,
            'extra' => array(),
        );

        foreach ($this->processors as $processor) {
            $record = call_user_func($processor, $record);
        }

        while ($handler = current($this->handlers)) {
            if (true === $handler->handle($record)) {
                break;
            }

            next($this->handlers);
        }

        return true;
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addDebug($message, array $context = array())
    {
        return $this->addRecord(static::DEBUG, $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addInfo($message, array $context = array())
    {
        return $this->addRecord(static::INFO, $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addNotice($message, array $context = array())
    {
        return $this->addRecord(static::NOTICE, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addWarning($message, array $context = array())
    {
        return $this->addRecord(static::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addError($message, array $context = array())
    {
        return $this->addRecord(static::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addCritical($message, array $context = array())
    {
        return $this->addRecord(static::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addAlert($message, array $context = array())
    {
        return $this->addRecord(static::ALERT, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addEmergency($message, array $context = array())
    {
        return $this->addRecord(static::EMERGENCY, $message, $context);
    }

    /**
     * Gets all supported logging levels.
     *
     * @return array Assoc array with human-readable level names => level codes.
     */
    public static function getLevels()
    {
        return array_flip(static::$levels);
    }

    /**
     * Gets the name of the logging level.
     *
     * @param  int    $level
     * @return string
     */
    public static function getLevelName($level)
    {
        if (!isset(static::$levels[$level])) {
            throw new InvalidArgumentException('Level "'.$level.'" is not defined, use one of: '.implode(', ', array_keys(static::$levels)));
        }

        return static::$levels[$level];
    }

    /**
     * Converts PSR-3 levels to Monolog ones if necessary
     *
     * @param string|int Level number (monolog) or name (PSR-3)
     * @return int
     */
    public static function toMonologLevel($level)
    {
        if (is_string($level) && defined(__CLASS__.'::'.strtoupper($level))) {
            return constant(__CLASS__.'::'.strtoupper($level));
        }

        return $level;
    }

    /**
     * Checks whether the Logger has a handler that listens on the given level
     *
     * @param  int     $level
     * @return Boolean
     */
    public function isHandling($level)
    {
        $record = array(
            'level' => $level,
        );

        foreach ($this->handlers as $handler) {
            if ($handler->isHandling($record)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a log record at an arbitrary level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  mixed   $level   The log level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function log($level, $message, array $context = array())
    {
        $level = static::toMonologLevel($level);

        return $this->addRecord($level, $message, $context);
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function debug($message, array $context = array())
    {
        return $this->addRecord(static::DEBUG, $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function info($message, array $context = array())
    {
        return $this->addRecord(static::INFO, $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function notice($message, array $context = array())
    {
        return $this->addRecord(static::NOTICE, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function warn($message, array $context = array())
    {
        return $this->addRecord(static::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function warning($message, array $context = array())
    {
        return $this->addRecord(static::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function err($message, array $context = array())
    {
        return $this->addRecord(static::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function error($message, array $context = array())
    {
        return $this->addRecord(static::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function crit($message, array $context = array())
    {
        return $this->addRecord(static::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function critical($message, array $context = array())
    {
        return $this->addRecord(static::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function alert($message, array $context = array())
    {
        return $this->addRecord(static::ALERT, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function emerg($message, array $context = array())
    {
        return $this->addRecord(static::EMERGENCY, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function emergency($message, array $context = array())
    {
        return $this->addRecord(static::EMERGENCY, $message, $context);
    }

    /**
     * Set the timezone to be used for the timestamp of log records.
     *
     * This is stored globally for all Logger instances
     *
     * @param \DateTimeZone $tz Timezone object
     */
    public static function setTimezone(\DateTimeZone $tz)
    {
        self::$timezone = $tz;
    }
}

//vendor/psr/log/Psr/Log/LoggerInterface.php
namespace Psr\Log;

/**
 * Describes a logger instance
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data, the only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 *
 * See https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * for the full interface specification.
 */
interface LoggerInterface
{
    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array());

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array());

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array());

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array());

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array());

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array());

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array());

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array());

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array());
}

//vendor/monolog/monolog/src/Monolog/Handler/StreamHandler.php
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Logger;

/**
 * Stores to any stream resource
 *
 * Can be used to store into php://stderr, remote and local files, etc.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class StreamHandler extends AbstractProcessingHandler
{
    protected $stream;
    protected $url;
    private $errorMessage;
    protected $filePermission;
    protected $useLocking;
    private $dirCreated;

    /**
     * @param resource|string $stream
     * @param int             $level          The minimum logging level at which this handler will be triggered
     * @param Boolean         $bubble         Whether the messages that are handled can bubble up the stack or not
     * @param int|null        $filePermission Optional file permissions (default (0644) are only for owner read/write)
     * @param Boolean         $useLocking     Try to lock log file before doing any writes
     *
     * @throws \Exception                If a missing directory is not buildable
     * @throws \InvalidArgumentException If stream is not a resource or string
     */
    public function __construct($stream, $level = Logger::DEBUG, $bubble = true, $filePermission = null, $useLocking = false)
    {
        parent::__construct($level, $bubble);
        if (is_resource($stream)) {
            $this->stream = $stream;
        } elseif (is_string($stream)) {
            $this->url = $stream;
        } else {
            throw new \InvalidArgumentException('A stream must either be a resource or a string.');
        }

        $this->filePermission = $filePermission;
        $this->useLocking = $useLocking;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->url && is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
    }

    /**
     * Return the currently active stream if it is open
     *
     * @return resource|null
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        if (!is_resource($this->stream)) {
            if (!$this->url) {
                throw new \LogicException('Missing stream url, the stream can not be opened. This may be caused by a premature call to close().');
            }
            $this->createDir();
            $this->errorMessage = null;
            set_error_handler(array($this, 'customErrorHandler'));
            $this->stream = fopen($this->url, 'a');
            if ($this->filePermission !== null) {
                @chmod($this->url, $this->filePermission);
            }
            restore_error_handler();
            if (!is_resource($this->stream)) {
                $this->stream = null;
                throw new \UnexpectedValueException(sprintf('The stream or file "%s" could not be opened: '.$this->errorMessage, $this->url));
            }
        }

        if ($this->useLocking) {
            // ignoring errors here, there's not much we can do about them
            flock($this->stream, LOCK_EX);
        }

        fwrite($this->stream, (string) $record['formatted']);

        if ($this->useLocking) {
            flock($this->stream, LOCK_UN);
        }
    }

    private function customErrorHandler($code, $msg)
    {
        $this->errorMessage = preg_replace('{^(fopen|mkdir)\(.*?\): }', '', $msg);
    }

    /**
     * @param string $stream
     *
     * @return null|string
     */
    private function getDirFromStream($stream)
    {
        $pos = strpos($stream, '://');
        if ($pos === false) {
            return dirname($stream);
        }

        if ('file://' === substr($stream, 0, 7)) {
            return dirname(substr($stream, 7));
        }

        return;
    }

    private function createDir()
    {
        // Do not try to create dir if it has already been tried.
        if ($this->dirCreated) {
            return;
        }

        $dir = $this->getDirFromStream($this->url);
        if (null !== $dir && !is_dir($dir)) {
            $this->errorMessage = null;
            set_error_handler(array($this, 'customErrorHandler'));
            $status = mkdir($dir, 0777, true);
            restore_error_handler();
            if (false === $status) {
                throw new \UnexpectedValueException(sprintf('There is no existing directory at "%s" and its not buildable: '.$this->errorMessage, $dir));
            }
        }
        $this->dirCreated = true;
    }
}

//vendor/monolog/monolog/src/Monolog/Handler/MailHandler.php
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

/**
 * Base class for all mail handlers
 *
 * @author Gyula Sallai
 */
abstract class MailHandler extends AbstractProcessingHandler
{
    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        $messages = array();

        foreach ($records as $record) {
            if ($record['level'] < $this->level) {
                continue;
            }
            $messages[] = $this->processRecord($record);
        }

        if (!empty($messages)) {
            $this->send((string) $this->getFormatter()->formatBatch($messages), $messages);
        }
    }

    /**
     * Send a mail with the given content
     *
     * @param string $content formatted email body to be sent
     * @param array  $records the array of log records that formed this content
     */
    abstract protected function send($content, array $records);

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->send((string) $record['formatted'], array($record));
    }

    protected function getHighestRecord(array $records)
    {
        $highestRecord = null;
        foreach ($records as $record) {
            if ($highestRecord === null || $highestRecord['level'] < $record['level']) {
                $highestRecord = $record;
            }
        }

        return $highestRecord;
    }
}

//vendor/monolog/monolog/src/Monolog/Handler/NativeMailerHandler.php
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;

/**
 * NativeMailerHandler uses the mail() function to send the emails
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @author Mark Garrett <mark@moderndeveloperllc.com>
 */
class NativeMailerHandler extends MailHandler
{
    /**
     * The email addresses to which the message will be sent
     * @var array
     */
    protected $to;

    /**
     * The subject of the email
     * @var string
     */
    protected $subject;

    /**
     * Optional headers for the message
     * @var array
     */
    protected $headers = array();

    /**
     * Optional parameters for the message
     * @var array
     */
    protected $parameters = array();

    /**
     * The wordwrap length for the message
     * @var int
     */
    protected $maxColumnWidth;

    /**
     * The Content-type for the message
     * @var string
     */
    protected $contentType = 'text/plain';

    /**
     * The encoding for the message
     * @var string
     */
    protected $encoding = 'utf-8';

    /**
     * @param string|array $to             The receiver of the mail
     * @param string       $subject        The subject of the mail
     * @param string       $from           The sender of the mail
     * @param int          $level          The minimum logging level at which this handler will be triggered
     * @param bool         $bubble         Whether the messages that are handled can bubble up the stack or not
     * @param int          $maxColumnWidth The maximum column width that the message lines will have
     */
    public function __construct($to, $subject, $from, $level = Logger::ERROR, $bubble = true, $maxColumnWidth = 70)
    {
        parent::__construct($level, $bubble);
        $this->to = is_array($to) ? $to : array($to);
        $this->subject = $subject;
        $this->addHeader(sprintf('From: %s', $from));
        $this->maxColumnWidth = $maxColumnWidth;
    }

    /**
     * Add headers to the message
     *
     * @param  string|array $headers Custom added headers
     * @return self
     */
    public function addHeader($headers)
    {
        foreach ((array) $headers as $header) {
            if (strpos($header, "\n") !== false || strpos($header, "\r") !== false) {
                throw new \InvalidArgumentException('Headers can not contain newline characters for security reasons');
            }
            $this->headers[] = $header;
        }

        return $this;
    }

    /**
     * Add parameters to the message
     *
     * @param  string|array $parameters Custom added parameters
     * @return self
     */
    public function addParameter($parameters)
    {
        $this->parameters = array_merge($this->parameters, (array) $parameters);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function send($content, array $records)
    {
        $content = wordwrap($content, $this->maxColumnWidth);
        $headers = ltrim(implode("\r\n", $this->headers) . "\r\n", "\r\n");
        $headers .= 'Content-type: ' . $this->getContentType() . '; charset=' . $this->getEncoding() . "\r\n";
        if ($this->getContentType() == 'text/html' && false === strpos($headers, 'MIME-Version:')) {
            $headers .= 'MIME-Version: 1.0' . "\r\n";
        }

        $subject = $this->subject;
        if ($records) {
            $subjectFormatter = new LineFormatter($this->subject);
            $subject = $subjectFormatter->format($this->getHighestRecord($records));
        }

        $parameters = implode(' ', $this->parameters);
        foreach ($this->to as $to) {
            mail($to, $subject, $content, $headers, $parameters);
        }
    }

    /**
     * @return string $contentType
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return string $encoding
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param  string $contentType The content type of the email - Defaults to text/plain. Use text/html for HTML
     *                             messages.
     * @return self
     */
    public function setContentType($contentType)
    {
        if (strpos($contentType, "\n") !== false || strpos($contentType, "\r") !== false) {
            throw new \InvalidArgumentException('The content type can not contain newline characters to prevent email header injection');
        }

        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @param  string $encoding
     * @return self
     */
    public function setEncoding($encoding)
    {
        if (strpos($encoding, "\n") !== false || strpos($encoding, "\r") !== false) {
            throw new \InvalidArgumentException('The encoding can not contain newline characters to prevent email header injection');
        }

        $this->encoding = $encoding;

        return $this;
    }
}

//vendor/monolog/monolog/src/Monolog/Handler/HandlerInterface.php
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Formatter\FormatterInterface;

/**
 * Interface that all Monolog Handlers must implement
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
interface HandlerInterface
{
    /**
     * Checks whether the given record will be handled by this handler.
     *
     * This is mostly done for performance reasons, to avoid calling processors for nothing.
     *
     * Handlers should still check the record levels within handle(), returning false in isHandling()
     * is no guarantee that handle() will not be called, and isHandling() might not be called
     * for a given record.
     *
     * @param array $record Partial log record containing only a level key
     *
     * @return Boolean
     */
    public function isHandling(array $record);

    /**
     * Handles a record.
     *
     * All records may be passed to this method, and the handler should discard
     * those that it does not want to handle.
     *
     * The return value of this function controls the bubbling process of the handler stack.
     * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
     * calling further handlers in the stack with a given log record.
     *
     * @param  array   $record The record to handle
     * @return Boolean true means that this handler handled the record, and that bubbling is not permitted.
     *                        false means the record was either not processed or that this handler allows bubbling.
     */
    public function handle(array $record);

    /**
     * Handles a set of records at once.
     *
     * @param array $records The records to handle (an array of record arrays)
     */
    public function handleBatch(array $records);

    /**
     * Adds a processor in the stack.
     *
     * @param  callable $callback
     * @return self
     */
    public function pushProcessor($callback);

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable
     */
    public function popProcessor();

    /**
     * Sets the formatter.
     *
     * @param  FormatterInterface $formatter
     * @return self
     */
    public function setFormatter(FormatterInterface $formatter);

    /**
     * Gets the formatter.
     *
     * @return FormatterInterface
     */
    public function getFormatter();
}

//vendor/monolog/monolog/src/Monolog/Formatter/NormalizerFormatter.php
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Formatter;

use Exception;

/**
 * Normalizes incoming records to remove objects/resources so it's easier to dump to various targets
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class NormalizerFormatter implements FormatterInterface
{
    const SIMPLE_DATE = "Y-m-d H:i:s";

    protected $dateFormat;

    /**
     * @param string $dateFormat The format of the timestamp: one supported by DateTime::format
     */
    public function __construct($dateFormat = null)
    {
        $this->dateFormat = $dateFormat ?: static::SIMPLE_DATE;
        if (!function_exists('json_encode')) {
            throw new \RuntimeException('PHP\'s json extension is required to use Monolog\'s NormalizerFormatter');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        return $this->normalize($record);
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    protected function normalize($data)
    {
        if (null === $data || is_scalar($data)) {
            if (is_float($data)) {
                if (is_infinite($data)) {
                    return ($data > 0 ? '' : '-') . 'INF';
                }
                if (is_nan($data)) {
                    return 'NaN';
                }
            }

            return $data;
        }

        if (is_array($data) || $data instanceof \Traversable) {
            $normalized = array();

            $count = 1;
            foreach ($data as $key => $value) {
                if ($count++ >= 1000) {
                    $normalized['...'] = 'Over 1000 items, aborting normalization';
                    break;
                }
                $normalized[$key] = $this->normalize($value);
            }

            return $normalized;
        }

        if ($data instanceof \DateTime) {
            return $data->format($this->dateFormat);
        }

        if (is_object($data)) {
            // TODO 2.0 only check for Throwable
            if ($data instanceof Exception || (PHP_VERSION_ID > 70000 && $data instanceof \Throwable)) {
                return $this->normalizeException($data);
            }

            // non-serializable objects that implement __toString stringified
            if (method_exists($data, '__toString') && !$data instanceof \JsonSerializable) {
                $value = $data->__toString();
            } else {
                // the rest is json-serialized in some way
                $value = $this->toJson($data, true);
            }

            return sprintf("[object] (%s: %s)", get_class($data), $value);
        }

        if (is_resource($data)) {
            return sprintf('[resource] (%s)', get_resource_type($data));
        }

        return '[unknown('.gettype($data).')]';
    }

    protected function normalizeException($e)
    {
        // TODO 2.0 only check for Throwable
        if (!$e instanceof Exception && !$e instanceof \Throwable) {
            throw new \InvalidArgumentException('Exception/Throwable expected, got '.gettype($e).' / '.get_class($e));
        }

        $data = array(
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile().':'.$e->getLine(),
        );

        $trace = $e->getTrace();
        foreach ($trace as $frame) {
            if (isset($frame['file'])) {
                $data['trace'][] = $frame['file'].':'.$frame['line'];
            } else {
                // We should again normalize the frames, because it might contain invalid items
                $data['trace'][] = $this->toJson($this->normalize($frame), true);
            }
        }

        if ($previous = $e->getPrevious()) {
            $data['previous'] = $this->normalizeException($previous);
        }

        return $data;
    }

    /**
     * Return the JSON representation of a value
     *
     * @param  mixed             $data
     * @param  bool              $ignoreErrors
     * @throws \RuntimeException if encoding fails and errors are not ignored
     * @return string
     */
    protected function toJson($data, $ignoreErrors = false)
    {
        // suppress json_encode errors since it's twitchy with some inputs
        if ($ignoreErrors) {
            return @$this->jsonEncode($data);
        }

        $json = $this->jsonEncode($data);

        if ($json === false) {
            $json = $this->handleJsonError(json_last_error(), $data);
        }

        return $json;
    }

    /**
     * @param  mixed  $data
     * @return string JSON encoded data or null on failure
     */
    private function jsonEncode($data)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return json_encode($data);
    }

    /**
     * Handle a json_encode failure.
     *
     * If the failure is due to invalid string encoding, try to clean the
     * input and encode again. If the second encoding iattempt fails, the
     * inital error is not encoding related or the input can't be cleaned then
     * raise a descriptive exception.
     *
     * @param  int               $code return code of json_last_error function
     * @param  mixed             $data data that was meant to be encoded
     * @throws \RuntimeException if failure can't be corrected
     * @return string            JSON encoded data after error correction
     */
    private function handleJsonError($code, $data)
    {
        if ($code !== JSON_ERROR_UTF8) {
            $this->throwEncodeError($code, $data);
        }

        if (is_string($data)) {
            $this->detectAndCleanUtf8($data);
        } elseif (is_array($data)) {
            array_walk_recursive($data, array($this, 'detectAndCleanUtf8'));
        } else {
            $this->throwEncodeError($code, $data);
        }

        $json = $this->jsonEncode($data);

        if ($json === false) {
            $this->throwEncodeError(json_last_error(), $data);
        }

        return $json;
    }

    /**
     * Throws an exception according to a given code with a customized message
     *
     * @param  int               $code return code of json_last_error function
     * @param  mixed             $data data that was meant to be encoded
     * @throws \RuntimeException
     */
    private function throwEncodeError($code, $data)
    {
        switch ($code) {
            case JSON_ERROR_DEPTH:
                $msg = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $msg = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $msg = 'Unexpected control character found';
                break;
            case JSON_ERROR_UTF8:
                $msg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $msg = 'Unknown error';
        }

        throw new \RuntimeException('JSON encoding failed: '.$msg.'. Encoding: '.var_export($data, true));
    }

    /**
     * Detect invalid UTF-8 string characters and convert to valid UTF-8.
     *
     * Valid UTF-8 input will be left unmodified, but strings containing
     * invalid UTF-8 codepoints will be reencoded as UTF-8 with an assumed
     * original encoding of ISO-8859-15. This conversion may result in
     * incorrect output if the actual encoding was not ISO-8859-15, but it
     * will be clean UTF-8 output and will not rely on expensive and fragile
     * detection algorithms.
     *
     * Function converts the input in place in the passed variable so that it
     * can be used as a callback for array_walk_recursive.
     *
     * @param mixed &$data Input to check and convert if needed
     * @private
     */
    public function detectAndCleanUtf8(&$data)
    {
        if (is_string($data) && !preg_match('//u', $data)) {
            $data = preg_replace_callback(
                '/[\x80-\xFF]+/',
                function ($m) { return utf8_encode($m[0]); },
                $data
            );
            $data = str_replace(
                array('¤', '¦', '¨', '´', '¸', '¼', '½', '¾'),
                array('€', 'Š', 'š', 'Ž', 'ž', 'Œ', 'œ', 'Ÿ'),
                $data
            );
        }
    }
}

//vendor/monolog/monolog/src/Monolog/Formatter/LineFormatter.php
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Formatter;

/**
 * Formats incoming records into a one-line string
 *
 * This is especially useful for logging to files
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Christophe Coevoet <stof@notk.org>
 */
class LineFormatter extends NormalizerFormatter
{
    const SIMPLE_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

    protected $format;
    protected $allowInlineLineBreaks;
    protected $ignoreEmptyContextAndExtra;
    protected $includeStacktraces;

    /**
     * @param string $format                     The format of the message
     * @param string $dateFormat                 The format of the timestamp: one supported by DateTime::format
     * @param bool   $allowInlineLineBreaks      Whether to allow inline line breaks in log entries
     * @param bool   $ignoreEmptyContextAndExtra
     */
    public function __construct($format = null, $dateFormat = null, $allowInlineLineBreaks = false, $ignoreEmptyContextAndExtra = false)
    {
        $this->format = $format ?: static::SIMPLE_FORMAT;
        $this->allowInlineLineBreaks = $allowInlineLineBreaks;
        $this->ignoreEmptyContextAndExtra = $ignoreEmptyContextAndExtra;
        parent::__construct($dateFormat);
    }

    public function includeStacktraces($include = true)
    {
        $this->includeStacktraces = $include;
        if ($this->includeStacktraces) {
            $this->allowInlineLineBreaks = true;
        }
    }

    public function allowInlineLineBreaks($allow = true)
    {
        $this->allowInlineLineBreaks = $allow;
    }

    public function ignoreEmptyContextAndExtra($ignore = true)
    {
        $this->ignoreEmptyContextAndExtra = $ignore;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $vars = parent::format($record);

        $output = $this->format;

        foreach ($vars['extra'] as $var => $val) {
            if (false !== strpos($output, '%extra.'.$var.'%')) {
                $output = str_replace('%extra.'.$var.'%', $this->stringify($val), $output);
                unset($vars['extra'][$var]);
            }
        }

        foreach ($vars['context'] as $var => $val) {
            if (false !== strpos($output, '%context.'.$var.'%')) {
                $output = str_replace('%context.'.$var.'%', $this->stringify($val), $output);
                unset($vars['context'][$var]);
            }
        }

        if ($this->ignoreEmptyContextAndExtra) {
            if (empty($vars['context'])) {
                unset($vars['context']);
                $output = str_replace('%context%', '', $output);
            }

            if (empty($vars['extra'])) {
                unset($vars['extra']);
                $output = str_replace('%extra%', '', $output);
            }
        }

        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%'.$var.'%')) {
                $output = str_replace('%'.$var.'%', $this->stringify($val), $output);
            }
        }

        return $output;
    }

    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }

    public function stringify($value)
    {
        return $this->replaceNewlines($this->convertToString($value));
    }

    protected function normalizeException($e)
    {
        // TODO 2.0 only check for Throwable
        if (!$e instanceof \Exception && !$e instanceof \Throwable) {
            throw new \InvalidArgumentException('Exception/Throwable expected, got '.gettype($e).' / '.get_class($e));
        }

        $previousText = '';
        if ($previous = $e->getPrevious()) {
            do {
                $previousText .= ', '.get_class($previous).'(code: '.$previous->getCode().'): '.$previous->getMessage().' at '.$previous->getFile().':'.$previous->getLine();
            } while ($previous = $previous->getPrevious());
        }

        $str = '[object] ('.get_class($e).'(code: '.$e->getCode().'): '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine().$previousText.')';
        if ($this->includeStacktraces) {
            $str .= "\n[stacktrace]\n".$e->getTraceAsString();
        }

        return $str;
    }

    protected function convertToString($data)
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return $this->toJson($data, true);
        }

        return str_replace('\\/', '/', @json_encode($data));
    }

    protected function replaceNewlines($str)
    {
        if ($this->allowInlineLineBreaks) {
            return $str;
        }

        return str_replace(array("\r\n", "\r", "\n"), ' ', $str);
    }
}

//vendor/monolog/monolog/src/Monolog/Formatter/FormatterInterface.php
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Formatter;

/**
 * Interface for formatters
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
interface FormatterInterface
{
    /**
     * Formats a log record.
     *
     * @param  array $record A record to format
     * @return mixed The formatted record
     */
    public function format(array $record);

    /**
     * Formats a set of log records.
     *
     * @param  array $records A set of records to format
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records);
}

//vendor/monolog/monolog/src/Monolog/Formatter/HtmlFormatter.php
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Formatter;

use Monolog\Logger;

/**
 * Formats incoming records into an HTML table
 *
 * This is especially useful for html email logging
 *
 * @author Tiago Brito <tlfbrito@gmail.com>
 */
class HtmlFormatter extends NormalizerFormatter
{
    /**
     * Translates Monolog log levels to html color priorities.
     */
    protected $logLevels = array(
        Logger::DEBUG     => '#cccccc',
        Logger::INFO      => '#468847',
        Logger::NOTICE    => '#3a87ad',
        Logger::WARNING   => '#c09853',
        Logger::ERROR     => '#f0ad4e',
        Logger::CRITICAL  => '#FF7708',
        Logger::ALERT     => '#C12A19',
        Logger::EMERGENCY => '#000000',
    );

    /**
     * @param string $dateFormat The format of the timestamp: one supported by DateTime::format
     */
    public function __construct($dateFormat = null)
    {
        parent::__construct($dateFormat);
    }

    /**
     * Creates an HTML table row
     *
     * @param  string $th       Row header content
     * @param  string $td       Row standard cell content
     * @param  bool   $escapeTd false if td content must not be html escaped
     * @return string
     */
    private function addRow($th, $td = ' ', $escapeTd = true)
    {
        $th = htmlspecialchars($th, ENT_NOQUOTES, 'UTF-8');
        if ($escapeTd) {
            $td = '<pre>'.htmlspecialchars($td, ENT_NOQUOTES, 'UTF-8').'</pre>';
        }

        return "<tr style=\"padding: 4px;spacing: 0;text-align: left;\">\n<th style=\"background: #cccccc\" width=\"100px\">$th:</th>\n<td style=\"padding: 4px;spacing: 0;text-align: left;background: #eeeeee\">".$td."</td>\n</tr>";
    }

    /**
     * Create a HTML h1 tag
     *
     * @param  string $title Text to be in the h1
     * @param  int    $level Error level
     * @return string
     */
    private function addTitle($title, $level)
    {
        $title = htmlspecialchars($title, ENT_NOQUOTES, 'UTF-8');

        return '<h1 style="background: '.$this->logLevels[$level].';color: #ffffff;padding: 5px;" class="monolog-output">'.$title.'</h1>';
    }

    /**
     * Formats a log record.
     *
     * @param  array $record A record to format
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        $output = $this->addTitle($record['level_name'], $record['level']);
        $output .= '<table cellspacing="1" width="100%" class="monolog-output">';

        $output .= $this->addRow('Message', (string) $record['message']);
        $output .= $this->addRow('Time', $record['datetime']->format($this->dateFormat));
        $output .= $this->addRow('Channel', $record['channel']);
        if ($record['context']) {
            $embeddedTable = '<table cellspacing="1" width="100%">';
            foreach ($record['context'] as $key => $value) {
                $embeddedTable .= $this->addRow($key, $this->convertToString($value));
            }
            $embeddedTable .= '</table>';
            $output .= $this->addRow('Context', $embeddedTable, false);
        }
        if ($record['extra']) {
            $embeddedTable = '<table cellspacing="1" width="100%">';
            foreach ($record['extra'] as $key => $value) {
                $embeddedTable .= $this->addRow($key, $this->convertToString($value));
            }
            $embeddedTable .= '</table>';
            $output .= $this->addRow('Extra', $embeddedTable, false);
        }

        return $output.'</table>';
    }

    /**
     * Formats a set of log records.
     *
     * @param  array $records A set of records to format
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }

    protected function convertToString($data)
    {
        if (null === $data || is_scalar($data)) {
            return (string) $data;
        }

        $data = $this->normalize($data);
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return str_replace('\\/', '/', json_encode($data));
    }
}

//lib/debug/LogManager.php
namespace litepubl\debug;

use Monolog\ErrorHandler;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use litepubl\Config;
use litepubl\utils\Filer;

class LogManager
{
    use \litepubl\core\AppTrait;
    const format = "%datetime%\n%channel%.%level_name%:\n%message%\n%context% %extra%\n\n";
    public $logger;
    public $runtime;

    public function __construct()
    {
        $logger = new logger('general');
        $this->logger = $logger;

        $app = $this->getApp();
        if (!Config::$debug) {
            $handler = new ErrorHandler($logger);
            $handler->registerErrorHandler([], false);
            //$handler->registerExceptionHandler();
            $handler->registerFatalHandler();
        }

        $handler = new StreamHandler($app->paths->data . 'logs/logs.log', Logger::DEBUG, true, 0666);
        $handler->setFormatter(new LineFormatter(static ::format, null, true, false));
        $logger->pushHandler($handler);

        $this->runtime = new RuntimeHandler(Logger::WARNING);
        $this->runtime->setFormatter(new EmptyFormatter());
        $logger->pushHandler($this->runtime);

        if (!Config::$debug && $app->installed) {
            $handler = new NativeMailerHandler($app->options->email, '[error] ' . $app->site->name, $app->options->fromemail, Logger::WARNING);
            $handler->setFormatter(new LineFormatter(static ::format, null, true, false));
            $logger->pushHandler($handler);
        }
    }

    public function logException(\Exception $e)
    {
        $log = "Caught exception:\n" . $e->getMessage() . "\n";
        $log.= LogException::getLog($e);
        $log = str_replace(dirname(dirname(__DIR__)) , '', $log);
        $this->logger->alert($log);
    }

    public function getTrace()
    {
        $log = LogException::trace();
        $log = str_replace(dirname(dirname(__DIR__)) , '', $log);
        return $log;
    }

    public function trace()
    {
        $this->logger->info($this->getTrace());
    }

    public function getHtml()
    {
        if (count($this->runtime->log)) {
            $formatter = new HtmlFormatter();
            $result = $formatter->formatBatch($this->runtime->log);
            //clear current log
            $this->runtime->log = [];
            return $result;
        }

        return '';
    }

    public static function old($mesg)
    {
        $log = date('r') . "\n";
        if (isset($_SERVER['REQUEST_URI'])) {
            $log.= $_SERVER['REQUEST_URI'] . "\n";
        }

        if (!is_string($s)) {
            $s = var_export($s, true);
        }

        $log.= $s;
        $log.= "\n";
        Filer::append(static ::getAppInstance()->paths->data . 'logs/filer.log', $log);
    }

}

//lib/debug/RuntimeHandler.php
namespace litepubl\debug;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Description of runtimeHandler
 *
 * @author Sinisa Culic  <sinisaculic@gmail.com>
 */

class RuntimeHandler extends AbstractProcessingHandler
{
    public $log;

    /**
     * @param integer $level  The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->log = [];
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->log[] = $record;
    }

}

//lib/debug/EmptyFormatter.php
namespace litepubl\debug;

class EmptyFormatter implements \Monolog\Formatter\FormatterInterface
{

    public function format(array $record)
    {
        return '';
    }

    public function formatBatch(array $records)
    {
        return '';
    }
}

//lib/debug/LogException.php
namespace litepubl\debug;

class LogException
{

    public static function getLog(\Exception $e)
    {
        return static ::getTraceLog($e->getTrace());
    }

    public static function trace()
    {
        return static ::getTraceLog(debug_backtrace());
    }

    public static function getTraceLog(array $trace)
    {
        $result = '';
        foreach ($trace as $i => $item) {
            if (isset($item['line'])) {
                $result.= sprintf('#%d %d %s ', $i, $item['line'], $item['file']);
            }

            if (isset($item['class'])) {
                $result.= $item['class'] . $item['type'] . $item['function'];
            } else {
                $result.= $item['function'] . '()';
            }

            if (isset($item['args']) && count($item['args'])) {
                $result.= "\n";
                $args = array();
                foreach ($item['args'] as $arg) {
                    $args[] = static ::dump($arg);
                }

                $result.= implode(', ', $args);
            }

            $result.= "\n";
        }

        return $result;
    }

    public static function dump(&$v)
    {
        switch (gettype($v)) {
            case 'string':
                if ((strlen($v) > 60) && ($i = strpos($v, ' ', 50))) {
                    $v = substr($v, 0, $i);
                }

                return sprintf('\'%s\'', $v);

            case 'object':
                return get_class($v);

            case 'boolean':
                return $v ? 'true' : 'false';

            case 'integer':
            case 'double':
            case 'float':
                return $v;

            case 'array':
                $result = '';
                foreach ($v as $k => $item) {
                    $s = static ::dump($item);
                    $result.= "$k = $s;\n";
                }

                return "[\n$result]\n";

            default:
                return gettype($v);
        }
    }

}

//litepubl.php
namespace litepubl\core;

use litepubl\config;

class litepubl
{
    public static $app;

    public static function init()
    {
        if (\version_compare(\PHP_VERSION, '5.4', '<')) {
            die('Lite Publisher requires PHP 5.4 or later. You are using PHP ' . \PHP_VERSION);
        }

        if (isset(config::$classes['app']) && class_exists(config::$classes['app'])) {
            $className = config::$classes['app'];
            static ::$app = new className();
        } else {
            static ::$app = new App();
        }

        static ::$app->run();
    }

}

litepubl::init();
