<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tclasses extends titems {
  public $classes;
  public $interfaces;
  public $remap;
  public $factories;
  public $instances;

  public static function i() {
    if (!isset(litepublisher::$classes)) {
      $class = __class__;
      litepublisher::$classes = new $class();
      litepublisher::$classes->instances[$class] = litepublisher::$classes;
    }
    return litepublisher::$classes;
  }

  public static function instance() {
    return self::i();
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
    if (function_exists('spl_autoload_register')) spl_autoload_register(array(
      $this,
      '_autoload'
    ));
  }

  public function load() {
    return tstorage::load($this);
  }

  public function save() {
    return tstorage::save($this);
  }

  public function getinstance($class) {
    if (!class_exists($class)) {
      $this->error("Class $class not found");
    }
    if (!isset($this->instances[$class])) {
      $this->instances[$class] = $this->newinstance($class);
    }
    return $this->instances[$class];
  }

  public function newinstance($class) {
    if (!empty($this->remap[$class])) $class = $this->remap[$class];
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
      require ($filename);
    }
  }

  public function getclassfilename($class, $debug = false) {
    if (isset($this->items[$class])) {
      $item = $this->items[$class];

      /*
       * item is indexed array
       * 0 = filename      * 1 = releative path      * 2 = filename for debug      */

      $filename = (litepublisher::$debug || $debug) && isset($item[2]) ? $item[2] : $item[0];
      if (Empty($item[1])) {
        return litepublisher::$paths->lib . $filename;
      }

      //may be is subdir
      $filename = trim($item[1], '\\/') . DIRECTORY_SEPARATOR . $filename;
      if (file_exists(litepublisher::$paths->plugins . $filename)) {
        return litepublisher::$paths->plugins . $filename;
      }

      if (file_exists(litepublisher::$paths->home . $filename)) {
        return litepublisher::$paths->home . $filename;
      }

      return false;
    } else if (isset($this->interfaces[$class])) {
      return litepublisher::$paths->lib . $this->interfaces[$class];
    }

    return false;
  }

  public function exists($class) {
    return isset($this->items[$class]);
  }

  public function getfactory($instance) {
    foreach ($this->factories as $classname => $factory) {
      if (@is_a($instance, $classname)) return $this->getinstance($factory);
    }
  }

  public function getresourcedir($c) {
    $class = self::get_class_name($c);
    $dir = dirname($this->getclassfilename($class));
    return $dir . '/resource/';
  }

} //class