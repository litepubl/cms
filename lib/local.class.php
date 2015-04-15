<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlocal {
  public static $self;
  public $loaded;
  public $ini;
  public $section;
  public $searchsect;
  
  public static function i($section = '') {
    if (!isset(self::$self)) {
      self::$self= getinstance(__class__);
      self::$self->loadfile('default');
    }
    if ($section != '') self::$self->section = $section;
    return self::$self;
  }
  
  public static function admin($section = '') {
    $result = self::i($section);
    $result->check('admin');
    return $result;
  }
  
  public function __construct() {
    $this->ini = array();
    $this->loaded = array();
    $this->searchsect = array('common', 'default');
  }
  
  public static function get($section, $key) {
    //if (!isset(self::i()->ini[$section][$key])) litepublisher::$options->error("$section:$key");
    return self::i()->ini[$section][$key];
  }
  
  public function __get($name) {
    if (isset($this->ini[$this->section][$name])) return $this->ini[$this->section][$name];
    foreach ($this->searchsect as $section) {
      if (isset($this->ini[$section][$name])) return $this->ini[$section][$name];
    }
    return '';
  }
  
  public function __isset($name) {
    if (isset($this->ini[$this->section][$name])) return true;
    foreach ($this->searchsect as $section) {
      if (isset($this->ini[$section][$name])) return true;
    }
    
    return false;
  }
  
  public function __call($name, $args) {
    return strtr ($this->__get($name), $args->data);
  }
  
  public function addsearch() {
    $this->joinsearch(func_get_args());
  }
  
  public function joinsearch(array $a) {
    foreach ($a as $sect) {
      $sect = trim(trim($sect), "\"',;:.");
      if (!in_array($sect, $this->searchsect)) $this->searchsect[] = $sect;
    }
  }
  
  public function firstsearch() {
    $a = array_reverse(func_get_args());
    foreach ($a as $sect) {
      $i = array_search($sect, $this->searchsect);
      if ($i !== false)         array_splice($this->searchsect, $i, 1);
      array_unshift($this->searchsect, $sect);
    }
  }
  
  public static function date($date, $format = '') {
    if (empty($format)) $format = self::i()->getdateformat();
    return self::i()->translate(date($format, $date), 'datetime');
  }
  
  public function getdateformat() {
    $format = litepublisher::$options->dateformat;
    return $format != ''? $format : $this->ini['datetime']['dateformat'];
  }
  
  public function translate($s, $section = 'default') {
    return strtr($s, $this->ini[$section]);
  }
  
  public function check($name) {
    if ($name == '') $name = 'default';
    if (!in_array($name, $this->loaded)) $this->loadfile($name);
  }
  
  public function loadfile($name) {
    $this->loaded[] = $name;
    $filename = self::getcachedir() . $name;
    if (tfilestorage::loadvar($filename, $v) && is_array($v)) {
      $this->ini = $v + $this->ini ;
      if (isset($v['searchsect'])) $this->joinsearch($v['searchsect']);
    } else {
      $merger = tlocalmerger::i();
      $merger->parse($name);
    }
  }
  
  public static function usefile($name) {
    self::i()->check($name);
    return self::$self;
  }
  
  public static function inifile($class, $filename) {
    return self::inicache(litepublisher::$classes->getresourcedir($class) . litepublisher::$options->language . $filename);
  }
  
  public static function inicache($filename) {
    $self = self::i();
    if (!isset(ttheme::$inifiles[$filename])) {
      $ini = ttheme::cacheini($filename);
      if (is_array($ini)) {
        $self->ini = $ini + $self->ini ;
        if (isset($ini['searchsect'])) $self->joinsearch($ini['searchsect']);
        $keys = array_keys($ini);
        $self->section = array_shift($keys);
        $self->addsearch($self->section);
      }
    }
    return $self;
  }
  
  //backward
  public static function loadlang($name) {
    self::usefile($name);
  }
  
  public static function getcachedir() {
    return litepublisher::$paths->data . 'languages' . DIRECTORY_SEPARATOR;
  }
  
  public static function clearcache() {
    tfiler::delete(self::getcachedir(), false, false);
    self::i()->loaded = array();
  }
  
}//class

class tdateformater {
  public  $date;
public function __construct($date) { $this->date = $date; }
public function __get($name) { return tlocal::translate(date($name, $this->date), 'datetime'); }
}