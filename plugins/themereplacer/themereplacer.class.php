<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tviewthemereplacer extends tview {
  
  protected function get_theme_instance($name) {
    return tthemereplacer::get_instance($name, $this->id);
  }
  
}//class

class tthemereplacer extends ttheme {
  protected $replace;
  protected $source;
  public $idview;
  
  public static function get_instance($name, $idview) {
    if (isset(self::$instances[$name])) return self::$instances[$name];
    $class = 'ttheme';
    if (isset(litepublisher::$classes->instances[$class])) {
      $result = litepublisher::$classes->instances[$class];
      if ($result->name != '') $result = litepublisher::$classes->newinstance(__class__);
    } else {
      $result = litepublisher::$classes->newinstance(__class__);
      litepublisher::$classes->instances[$class] = $result;
    }
    
    $result->idview = $idview;
    $result->name = $name;
    $result->load();
    return $result;
  }
  
  public function getbasename() {
    return 'themes' . DIRECTORY_SEPARATOR . $this->idview . '.replacer.' . $this->name;
  }
  
  public function parsetheme() {
    if ($this->name == '') return false;
    if (!isset($this->source)) {
      $this->source = new ttheme();
    }
    
    if (!isset($this->replace)) {
      $this->replace = titemsreplacer::i()->items[$this->idview];
    }
    
    $this->source->name = $this->name;
    $this->source->load();
    self::$instances[$this->name] = $this;
    $this->templates = $this->replace + $this->source->templates;
    $this->save();
    return true;
  }
  
  
public function install() {}
public function uninstall() {}
}//class

class titemsreplacer extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename=  'plugins' .DIRECTORY_SEPARATOR  . strtolower(get_class($this));
  }
  
  public function add($id) {
    $this->items[$id] = array();
    $this->save();
  }
  
}//class