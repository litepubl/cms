<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tclasses extends titems {
  public $classes;
  public $interfaces;
  public $remap;
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
    $this->addevents('onnewitem', 'gettemplatevar');
    $this->addmap('classes', array());
    $this->addmap('interfaces', array());
    $this->addmap('remap', array());
    $this->addmap('factories', array());
    $this->instances = array();
    if (function_exists('spl_autoload_register')) {
      spl_autoload_register(array(
        $this,
        '_autoload'
      ));
    }
  }

  public function getstorage() {
    return litepubl::$datastorage;
  }

  public function getinstance($class) {
    if (isset($this->instances[$class])) {
return $this->instances[$class];
}

if (!($newclass = $this->class_exists($class))) {
      $this->error(sprintf('Class $class "%s" not found', $class));
}

    if (($newclass != $class) && isset($this->instances[$newclass])) {
//\class_alias($newclass, $class);
      $this->instances[$class] = $this->instances[$newclass];
return $this->instances[$newclass];
}

$instance = $this->newinstance($newclass);
      $this->instances[$class] = $instance;
if ($newclass != $class) {
      $this->instances[$newclass] = $instance;
}

    return $instance;
  }

public function class_exists($classname) {
    if (class_exists($classname)) {
return $classname;
}

if (!strpos($classname, '\\') && class_exists('litepubl\\' . $classname, false)) {
return 'litepubl\\'  . $classname;
}

return false;
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
    if (isset($this->classes[$name])) return $this->getinstance($this->classes[$name]);
    if (isset($this->items[$name])) return $this->getinstance($name);
    $class = 't' . $name;
    if (isset($this->items[$class])) return $this->getinstance($class);
    return parent::__get($name);
  }

  public function add($class, $filename, $path = '') {
    if (isset($this->items[$class]) && ($this->items[$class][0] == $filename) && ($this->items[$class][1] == $path)) return false;

    $this->lock();
    $this->items[$class] = array(
      $filename,
      $path
    );

    $instance = $this->getinstance($class);
    if (method_exists($instance, 'install')) $instance->install();
    $this->unlock();
    $this->added($class);
    return true;
  }

  public function delete($class) {
    if (!isset($this->items[$class])) return false;
    $this->lock();
    if (class_exists($class)) {
      $instance = $this->getinstance($class);
      if (method_exists($instance, 'uninstall')) $instance->uninstall();
    }

    unset($this->items[$class]);
    $this->unlock();
    $this->deleted($class);
  }

  public function reinstall($class) {
    if (isset($this->items[$class])) {
      $this->lock();
      $item = $this->items[$class];
      $this->delete($class);
      $this->add($class, $item[0], $item[1]);
      $this->unlock();
    }
  }

  public function _autoload($class) {
    if ($filename = $this->getclassfilename($class)) {
      $this->include_file($filename);
    }
  }

  public function include_file($filename) {
    if (file_exists($filename)) {
      require_once ($filename);
    }
  }

  public function getclassfilename($class, $debug = false) {
    if (isset($this->items[$class])) {
      $item = $this->items[$class];
    } else if (($subclass = basename($class)) && ($subclass != $class) && isset($this->items[$subclass])) {
      $item = $this->items[$subclass];
    } else if (isset($this->interfaces[$class])) {
      return litepubl::$paths->lib . $this->interfaces[$class];
    } else if ($subclass && ($subclass != $class) && isset($this->interfaces[$subclass])) {
      return litepubl::$paths->lib . $this->interfaces[$subclass];
    } else {
      return false;
    }

    /*
     * item is indexed array
     * 0 = filename
     * 1 = releative path
     * 2 = filename for debug
    */

    $filename = (litepubl::$debug || $debug) && isset($item[2]) ? $item[2] : $item[0];
    if (Empty($item[1])) {
      return litepubl::$paths->lib . $filename;
    }

    //may be is subdir
    $filename = trim($item[1], '\\/') . DIRECTORY_SEPARATOR . $filename;
    if (file_exists(litepubl::$paths->plugins . $filename)) {
      return litepubl::$paths->plugins . $filename;
    }

    if (file_exists(litepubl::$paths->home . $filename)) {
      return litepubl::$paths->home . $filename;
    }

    return false;
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
        return $this->getinstance($factory);
      }
    }
  }

  public function getresourcedir($c) {
    $class = static::get_class_name($c);
    $dir = dirname($this->getclassfilename($class));
    return $dir . '/resource/';
  }

} //class