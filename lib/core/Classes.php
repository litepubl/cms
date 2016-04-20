<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

class Classes extends Items
{
    public $namespaces;
    public $kernel;
    public $remap;
public $classmap;
    public $aliases;
    public $instances;
public $loaded;

    public static function i() {
        if (!isset(litepubl::$app->classes)) {
            $classname = get_called_class();
            litepubl::$app->classes = new $classname();
            litepubl::$app->classes->instances[$classname] =  $this->getApp()->classes;
        }

        return litepubl::$app->classes;
    }

    protected function create() {
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

        spl_autoload_register(array(
            $this,
            'autoload'
        ));
    }

    public function getStorage() {
        return  $this->getApp()->datastorage;
    }

    public function getInstance($class) {
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

    public function newinstance($class) {
        if (!empty($this->remap[$class])) {
            $class = $this->remap[$class];
        }

        return new $class();
    }

    public function newitem($name, $class, $id) {
        if (!empty($this->remap[$class])) $class = $this->remap[$class];
        $this->callevent('onnewitem', array(
            $name, &$class,
            $id
        ));
        return new $class();
    }

    public function add($class, $filename, $deprecatedPath = false) {
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

    public function installClass($classname) {
        $instance = $this->getinstance($classname);
        if (method_exists($instance, 'install')) {
            $instance->install();
        }

        return $instance;
    }

    public function uninstallClass($classname) {
        if (class_exists($classname)) {
            $instance = $this->getinstance($classname);
            if (method_exists($instance, 'uninstall')) {
                $instance->uninstall();
            }
        }
    }

    public function delete($class) {
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

    public function reinstall($class) {
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

    public function baseclass($classname) {
        if ($i = strrpos($classname, '\\')) {
            return substr($classname, $i + 1);
        }

        return $classname;
    }

    public function addAlias($classname, $alias) {
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

public function getClassmap($classname) {
if (isset($this->aliases[$classname])) {
return $this->aliases[$classname];
}

if (!count($this->classmap)) {
$this->classmap = include( $this->getApp()->paths->lib . 'update/classmap.php');
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

    public function autoload($classname) {
if (isset($this->loaded[$classname])) {
return;
}

if (config::$useKernel && !config::$debug &&
($filename = $this->findKernel($classname))) {
$this->loaded[$classname] = $filename;
include $filename;
if (class_exists($classname, false) || interface_exists($classname, false) || trait_exists($classname, false)) {
return;
}
}

$filename = $this->findFile($classname);
$this->loaded[$classname] = $filename;
if ($filename) {
include $filename;
}
}

public function findFile($classname) {
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

    public function findClassmap($classname) {
        if (isset($this->items[$classname])) {
            $filename = $this->app->paths->home . $this->items[$classname];
if (file_exists($filename)) {
return $filename;
}
        }
   }

    public function include ($filename) {
        //if (is_dir($filename)) $this->error($filename);
        require_once $filename;
    }

    public function include_file($filename) {
        if ($filename && file_exists($filename)) {
            $this->include($filename);
        }
    }

    public function findPSR4($classname) {
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
$dir =$paths->home . $this->namespaces[$ns] . '/';
                $filename = $dir . $baseclass . '.php';
                if (file_exists($filename)) {
$this->loaded[$ns] = $dir;
                    return $filename;
                }
            }

$names = explode('\\', $ns);
$vendor = array_shift($names);
while(count($names)) {
if (isset($this->namespaces[$vendor])) {
                    $dir = $paths->home . $this->namespaces[$vendor] . '/' . implode('/', $names) . '/';
                    $filename = $dir . $baseclass . '.php';
                    if (file_exists($filename)) {
$this->loaded[$ns] = $dir;
                        return $filename;
                    }
                }

$vendor .= '\\' . array_shift($names);
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

    public function findKernel($classname) {
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
while(count($names)) {
if (isset($this->namespaces[$vendor])) {
                    $dir = $paths->home . $this->namespaces[$vendor] . '/' . implode('/', $names) . '/';
                    $filename = $dir . 'kernel.php';
                    if (file_exists($filename)) {
$this->loaded[$ns] = $dir;
                        return $filename;
                    }
                }

$vendor .= '\\' . array_shift($names);
            }

                $dir = $home . $ns . '/';
                $filename = $dir . 'kernel.php';
                if (file_exists($filename)) {
$this->loaded[$ns] = $dir;
                    return $filename;
                }

        return false;
    }

    public function subSpace($namespace, $root) {
        return str_replace('\\', DIRECTORY_SEPARATOR, strtolower(substr($namespace, strlen($root) + 1)));
    }

    public function exists($class) {
        return isset($this->items[$class]);
    }

    public function rename($oldclass, $newclass) {
        if (isset($this->items[$oldclass])) {
            $this->items[$newclass] = $this->items[$oldclass];
            unset($this->items[$oldclass]);
            if (isset($this->kernel[$oldclass])) {
                $this->kernel[$newclass] = $this->items[$oldclass];
                unset($this->kernel[$oldclass]);
            }

             $this->getApp()->router->db->update('class =' . Str::uuote($newclass) , 'class = ' . Str::uuote($oldclass));
            $this->save();
            $this->onrename($oldclass, $newclass);
        }
    }

    public function getResourcedir($c) {
        $reflector = new \ReflectionClass($c);
        $filename = $reflector->getFileName();
        return dirname($filename) . '/resource/';
    }

    public function getThemeVar($name) {
        $result = false;
        if (isset($this->instances[$name])) {
            $result = $this->instances[$name];
        } elseif ($filename = $this->findPSR4($name)) {
            $this->include($filename);
            $result = $this->getinstance($name);
        } elseif (isset($this->classes[$name])) {
            $result = $this->getinstance($this->classes[$name]);
        } elseif (isset($this->items[$name])) {
            $result = $this->getinstance($name);
        }

        return $result;
    }

}