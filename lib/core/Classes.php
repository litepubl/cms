<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;

class Classes extends Items
{
    public $namespaces;
    public $kernel;
    public $classes;
    public $remap;
public $classmap;
    public $aliases;
    public $factories;
    public $instances;

    public static function i() {
        if (!isset(litepubl::$classes)) {
            $classname = get_called_class();
            litepubl::$classes = new $classname();
            litepubl::$classes->instances[$classname] = litepubl::$classes;
        }

        return litepubl::$classes;
    }

    protected function create() {
        parent::create();
        $this->basename = 'classes';
        $this->dbversion = false;
        $this->addevents('onnewitem', 'onrename');
        $this->addmap('namespaces', array());
        $this->addmap('kernel', array());
        $this->addmap('classes', array());
        $this->addmap('remap', array());
        $this->addmap('factories', array());
        $this->instances = array();
$this->classmap = [];
        $this->aliases = array();

        spl_autoload_register(array(
            $this,
            'autoload'
        ));
    }

    public function getstorage() {
        return litepubl::$datastorage;
    }

    public function getinstance($class) {
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

    public function __get($name) {
        if (isset($this->classes[$name])) {
            $result = $this->getinstance($this->classes[$name]);
        } else if (isset($this->items[$name])) {
            $result = $this->getinstance($name);
        } else if (isset($this->items['t' . $class])) {
            $result = $this->getinstance('t' . $class);
        } else {
            $result = parent::__get($name);
        }

        return $result;
    }

    public function add($class, $filename, $deprecatedPath = false) {
        if ($incfilename = $this->getpsr4($class)) {
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
$this->classmap = include(litepubl::$paths->lib . 'update/classmap.php');
}
$classname = $this->baseclass($classname);
if (isset($this->classmap($classname) {
$result = $this->classmap[$classname];
if (!isset($this->aliases[$classname]) {
class_alias($result, $classname, false);
$this->aliases[$classname] = $result;
}

$classname = 'litepubl\\' . $classname;
if (!isset($this->aliases[$classname]) {
class_alias($result, $classname, false);
$this->aliases[$classname] = $result;
}

return $result;
}

return false;
}

    public function autoload($classname) {
if ($newclass = $this->getClassmap($classname)) {
$classname = $newclass;
}

        if ($filename = $this->getpsr4($classname)) {
            $this->include($filename);
        } else if (!config::$useKernel || litepubl::$debug || !$this->includeKernel($classname)) {
            $this->includeClass($classname);
        }
    }

    public function includeClass($classname) {
        if (isset($this->items[$classname])) {
            $filename = litepubl::$paths->home . $this->items[$classname];
            $this->include_file($filename);
            $this->addAlias($classname, false);
        } else if (($subclass = $this->baseclass($classname)) && ($subclass != $classname) && isset($this->items[$subclass])) {
            $filename = litepubl::$paths->home . $this->items[$subclass];
            $this->include_file($filename);
            $this->addAlias($classname, $subclass);
        } else if (!strpos($classname, '\\') && isset($this->items['litepubl\\' . $classname])) {
            $filename = litepubl::$paths->home . $this->items['litepubl\\' . $classname];
            $this->include_file($filename);
            $this->addAlias('litepubl\\' . $classname, $classname);
        } else {
            return false;
        }

        return $filename;
    }

    public function includeKernel($classname) {
        if (isset($this->kernel[$classname])) {
            $filename = litepubl::$paths->home . $this->kernel[$classname];
            $this->include_file($filename);
            $this->addAlias($classname, false);
        } else if (($subclass = $this->baseclass($classname)) && ($subclass != $classname) && isset($this->kernel[$subclass])) {
            $filename = litepubl::$paths->home . $this->kernel[$subclass];
            $this->include_file($filename);
            $this->addAlias($classname, $subclass);
        } else if (!strpos($classname, '\\') && isset($this->kernel['litepubl\\' . $classname])) {
            $filename = litepubl::$paths->home . $this->kernel['litepubl\\' . $classname];
            $this->include_file($filename);
            $this->addAlias('litepubl\\' . $classname, $classname);
        } else {
            return false;
        }

        return $filename;
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

    public function getpsr4($classname) {
        if ($i = strrpos($classname, '\\')) {
            $ns = substr($classname, 0, $i);
            $baseclass = substr($classname, $i + 1);

            if ($ns == 'litepubl') {
                $filename = litepubl::$paths->lib . $baseclass . '.php';
                if (file_exists($filename)) {
                    return $filename;
                }

                return false;
            }

            if (config::$useKernel && !litepubl::$debug && isset($this->kernel[$ns])) {
                $filename = litepubl::$paths->home . $this->kernel[$ns];
                if (file_exists($filename)) {
                    return $filename;
                }
            }

            if (isset($this->namespaces[$ns])) {
                $filename = litepubl::$paths->home . sprintf('%s/%s.php', $this->namespaces[$ns], $baseclass);

                if (file_exists($filename)) {
                    return $filename;
                }
            }

            foreach ($this->namespaces as $name => $dir) {
                if (strbegin($ns, $name)) {
                    $filename = litepubl::$paths->home . sprintf('%s%s/%s.php', $this->namespaces[$name], $this->subSpace($ns, $name) , $baseclass);

                    if (file_exists($filename)) {
                        return $filename;
                    }
                }
            }

            //last chanse
            $name = 'litepubl\plugins';
            if (strbegin($ns, $name)) {
                $filename = litepubl::$paths->plugins . sprintf('%s/%s.php', $this->subSpace($ns, $name) , $baseclass);

                if (file_exists($filename)) {
                    return $filename;
                }
            }
        }

        return false;
    }

    public function subSpace($namespace, $root) {
        return str_replace('\\', DIRECTORY_SEPARATOR, strtolower(substr($namespace, strlen($root) + 1)));
    }

    public function exists($class) {
        return isset($this->items[$class]);
    }

    public function getfactory($instance) {
        foreach ($this->factories as $classname => $factory) {
            //fix namespace
            if (!strpos($classname, '\\')) {
                $classname = 'litepubl\\' . $classname;
            }

            if (is_a($instance, $classname)) {
                if (!strpos($factory, '\\')) {
                    $factory = 'litepubl\\' . $factory;
                }

                return $this->getinstance($factory);
            }
        }
    }

    public function rename($oldclass, $newclass) {
        if (isset($this->items[$oldclass])) {
            $this->items[$newclass] = $this->items[$oldclass];
            unset($this->items[$oldclass]);
            if (isset($this->kernel[$oldclass])) {
                $this->kernel[$newclass] = $this->items[$oldclass];
                unset($this->kernel[$oldclass]);
            }

            litepubl::$urlmap->db->update('class =' . dbquote($newclass) , 'class = ' . dbquote($oldclass));
            $this->save();
            $this->onrename($oldclass, $newclass);
        }
    }

    public function getresourcedir($c) {
        $reflector = new \ReflectionClass($c);
        $filename = $reflector->getFileName();
        return dirname($filename) . '/resource/';
    }

    public function getThemeVar($name) {
        $result = false;
        if (isset($this->instances[$name])) {
            $result = $this->instances[$name];
        } elseif ($filename = $this->getpsr4($name)) {
            $this->include($filename);
            $result = $this->getinstance($name);
        } elseif (isset($this->classes[$name])) {
            $result = $this->getinstance($this->classes[$name]);
        } elseif (isset($this->items[$name])) {
            $result = $this->getinstance($name);
        } elseif (isset($this->items['t' . $class])) {
            $result = $this->getinstance('t' . $class);
        }

        return $result;
    }

} //class