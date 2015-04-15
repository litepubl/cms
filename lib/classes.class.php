<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

//fix storage include
if (!class_exists('tstorage')) {
include (dirname(__file__) . '/storage.class.php');
}

if (!function_exists( 'spl_autoload_register' ) ) {
  function __autoload($class) {
    litepublisher::$classes->_autoload($class);
  }
}

class tclasses extends titems {
  public $classes;
  public $interfaces;
  public $remap;
  public $factories;
  public $instances;
  public $included_files;
  
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
    if (function_exists('spl_autoload_register')) spl_autoload_register(array($this, '_autoload'));
    $this->data['memcache'] = false;
    $this->data['revision_memcache'] = 1;
    $this->included_files = array();
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
    /*
    if (preg_match('/^(tcomments|toptions|tsite|targs|ttheme)$/', $class)) return new $class();
    return new tdebugproxy(new $class());
    */
  }
  
  public function newitem($name, $class, $id) {
    if (!empty($this->remap[$class])) $class = $this->remap[$class];
    $this->callevent('onnewitem', array($name, &$class, $id));
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
    if (isset($this->items[$class]) &&
    ($this->items[$class][0] == $filename) && ($this->items[$class][1] == $path)) return false;
    
    $this->lock();
    $m = $this->memcache;
    $this->memcache = false;
    $this->items[$class] = array($filename, $path);
    $instance = $this->getinstance($class);
    if (method_exists($instance, 'install')) $instance->install();
    $this->memcache = $m;
    if ($m) $this->revision_memcache++;
    $this->unlock();
    $this->added($class);
    return true;
  }
  
  public function delete($class) {
    if (!isset($this->items[$class])) return false;
    $this->lock();
    $m = $this->memcache;
    $this->memcache = false;
    
    if (class_exists($class)) {
      $instance = $this->getinstance($class);
      if (method_exists($instance, 'uninstall')) $instance->uninstall();
    }
    
    unset($this->items[$class]);
    
    $this->memcache = $m;
    if ($m) $this->revision_memcache++;
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
      $this->included_files[$class] = $filename;
    }
  }
  
  public function include_file($filename) {
    if (!tfilestorage::$memcache || litepublisher::$debug  || !$this->memcache) {
      if (file_exists($filename)) require_once($filename);
      return;
    }
    
    if (in_array($filename, $this->included_files)) return;
    
    if ($s =  tfilestorage::$memcache->get($filename)) {
      $i = strpos($s, ';');
      $revision = substr($s, 0, $i);
      if ($revision == $this->revision_memcache) {
        eval(substr($s, $i + 1));
        return;
      }
      tfilestorage::$memcache->delete($filename);
    }
    
    if (file_exists($filename)) {
      $s = file_get_contents($filename);
      eval('?>' . $s);
      //strip php tag and copyright in head
      if (strbegin($s, '<?php')) $s = substr($s, 5);
      if (strend($s, '?>')) $s = substr($s, 0, -2);
      $s = trim($s);
      if (strbegin($s, '/*')) $s = substr($s, strpos($s, '*/') + 2);
      $s = $this->revision_memcache . ';' . ltrim($s);
      tfilestorage::$memcache->set($filename, $s, false, 3600);
    }
  }
  
  public function getclassfilename($class, $debug = false) {
    if (isset($this->items[$class])) {
      $item = $this->items[$class];
      $filename = (litepublisher::$debug || $debug) && isset($item[2]) ? $item[2] : $item[0];
      if (Empty($item[1])) {
        return litepublisher::$paths->lib . $filename;
      }
      $filename = trim($item[1], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
      //if (file_exists($filename))  return $filename;
      //may be is subdir?
      if (file_exists(litepublisher::$paths->plugins . $filename)) return litepublisher::$paths->plugins . $filename;
      if (file_exists(litepublisher::$paths->themes . $filename)) return litepublisher::$paths->themes . $filename;
      if  (file_exists(litepublisher::$paths->home . $filename)) return  litepublisher::$paths->home . $filename;
    }
    if (isset($this->interfaces[$class])) return litepublisher::$paths->lib . $this->interfaces[$class];
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
    if (isset($this->included_files[$class])) {
      $dir = dirname($this->included_files[$class]);
    } else {
      $dir = dirname($this->getclassfilename($class));
    }
    
    return $dir . '/resource/';
  }
  
}//class

function getinstance($class) {
  return litepublisher::$classes->getinstance($class);
}