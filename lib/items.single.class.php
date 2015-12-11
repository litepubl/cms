<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tsingleitems extends titems {
  public static $instances;
  public $id;
  
  public static function singleinstance($class, $id = 0) {
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$class][$id]))     return self::$instances[$class][$id];
    $self = litepublisher::$classes->newinstance($class);
    self::$instances[$class][$id] = $self;
    $self->id = $id;
    $self->load();
    return $self;
  }
  
  public function load() {
    if (!isset($this->id)) return false;
    return parent::load();
  }
  
  public function free() {
    unset(self::$instances[get_class($this)][$this->id]);
  }
  
}//class