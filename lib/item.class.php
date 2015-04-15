<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class titem extends tdata {
  public static $instances;
  //public $id;
  
  public static function iteminstance($class, $id = 0) {
    $name = call_user_func_array(array($class, 'getinstancename'), array());
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$name][$id]))     return self::$instances[$name][$id];
    $self = litepublisher::$classes->newitem($name, $class, $id);
    return $self->loaddata($id);
  }
  
  public function loaddata($id) {
    $this->data['id'] = $id;
    if ($id != 0) {
      if (!$this->load()) {
        $this->free();
        return false;
      }
      self::$instances[$this->instancename][$id] = $this;
    }
    return $this;
  }
  
  public function free() {
    unset(self::$instances[$this->getinstancename()][$this->id]);
  }
  
  public function __construct() {
    parent::__construct();
    $this->data['id'] = 0;
  }
  
  public function __destruct() {
    $this->free();
  }
  
  public function __set($name, $value) {
    if (parent::__set($name, $value)) return true;
    return  $this->Error("Field $name not exists in class " . get_class($this));
  }
  
  public function setid($id) {
    if ($id != $this->id) {
      $name = $this->instancename;
      if (!isset(self::$instances)) self::$instances = array();
      if (!isset(self::$instances[$name])) self::$instances[$name] = array();
      $a = &self::$instances[$this->instancename];
      if (isset(   $a[$this->id])) unset($a[$this->id]);
      if (isset($a[$id])) $a[$id] = 0;
      $a[$id] = $this;
      $this->data['id'] = $id;
    }
  }
  
  public function request($id) {
    if ($id != $this->id) {
      $this->setid($id);
      if (!$this->load()) return 404;
    }
  }
  
  public static function deletedir($dir) {
    if (!@file_exists($dir)) return false;
    tfiler::delete($dir, true, true);
    @rmdir($dir);
  }
  
}

class titem_storage extends titem {
  
  public function getowner() {
    $this->error(sprintf('The "%s" no have owner', get_class($this)));
  }
  
  public function load() {
    $owner = $this->owner;
    if ($owner->itemexists($this->id)) {
      $this->data = &$owner->items[$this->id];
      return true;
    }
    return false;
  }
  
  public function save() {
    return $this->owner->save();
  }
  
}//class