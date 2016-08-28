<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\core;

/**
 * This is the base class to storage data
 *
 * @property-read App $app
 * @property-read Storage $storage
 * @property-read DB $db
 * @property-read string $thisTable
 */

class Data
{
    const ZERODATE = '0000-00-00 00:00:00';
    public static $guid = 0;
    public $basename;
    public $data;
    public $lockcount;
    public $table;

    public static function i()
    {
        return static ::iGet(get_called_class());
    }

    public static function iGet(string $class)
    {
        return static ::getAppInstance()->classes->getInstance($class);
    }

    public static function getAppInstance(): App
    {
        return litepubl::$app;
    }

    public function __construct()
    {
        $this->data = [];
        $this->lockcount = 0;

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

    public function __destruct()
    {
        $this->free();
    }

    public function free()
    {
    }

    public function __get($name)
    {
        if (method_exists($this, $get = 'get' . $name)) {
            return $this->$get();
        } elseif (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            return $this->getProp($name);
        }
    }

    protected function getProp(string $name)
    {
            throw new PropException(get_class($this), $name);
    }

    public function __set($name, $value)
    {
        if (method_exists($this, $set = 'set' . $name)) {
            $this->$set($value);
        } elseif (array_key_exists($name, $this->data)) {
            $this->data[$name] = $value;
        } else {
            $this->setProp($name, $value);
        }
    }

    protected function setProp(string $name, $value)
    {
            throw new PropException(get_class($this), $name);
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->data) || method_exists($this, "get$name") || method_exists($this, "Get$name");
    }

    public function __call($name, $params)
    {
            throw new \UnexpectedValueException(sprintf('Call unknown method %s in %s', $name, get_class($this)));
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

    public function getApp(): App
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

        include_once $file;

        $fnc = (is_object($class) ? get_class($class) : (string) $class) . $func;
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
                $args = [
                    $this,
                    $args
                ];
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

    public function afterLoad()
    {
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

    public function getClass(): string
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

    protected function getThisTable()
    {
        return $this->getApp()->db->prefix . $this->table;
    }

    public static function getClassName($c): string
    {
        return is_object($c) ? get_class($c) : trim($c);
    }
}
