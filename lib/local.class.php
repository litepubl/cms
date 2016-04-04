<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tlocal {
  public static $self;
  public $loaded;
  public $ini;
  public $section;
  public $searchsect;

  public static function i($section = '') {
    if (!isset(static::$self)) {
      static::$self = static::getinstance();
      static::$self->loadfile('default');
    }

    if ($section != '') static::$self->section = $section;
    return static::$self;
  }

public static function getinstance() {
return litepubl::$classes->getinstance(get_called_class());
}

  public static function admin($section = '') {
    $result = static::i($section);
    $result->check('admin');
    return $result;
  }

  public function __construct() {
    $this->ini = array();
    $this->loaded = array();
    $this->searchsect = array(
      'common',
      'default'
    );
  }

  public static function get($section, $key) {
    return static::i()->ini[$section][$key];
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
    return strtr($this->__get($name) , $args->data);
  }

  public function addsearch() {
    $this->joinsearch(func_get_args());
  }

  public function joinsearch(array $a) {
    foreach ($a as $sect) {
      $sect = trim(trim($sect) , "\"',;:.");
      if (!in_array($sect, $this->searchsect)) $this->searchsect[] = $sect;
    }
  }

  public function firstsearch() {
    $a = array_reverse(func_get_args());
    foreach ($a as $sect) {
      $i = array_search($sect, $this->searchsect);
      if ($i !== false) array_splice($this->searchsect, $i, 1);
      array_unshift($this->searchsect, $sect);
    }
  }

  public static function date($date, $format = '') {
    if (empty($format)) $format = static::i()->getdateformat();
    return static::i()->translate(date($format, $date) , 'datetime');
  }

  public function getdateformat() {
    $format = litepubl::$options->dateformat;
    return $format != '' ? $format : $this->ini['datetime']['dateformat'];
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
    $filename = static::getcachedir() . $name;
    if (($data = litepubl::$storage->loaddata($filename)) && is_array($data)) {
      $this->ini = $data + $this->ini;
      if (isset($data['searchsect'])) {
        $this->joinsearch($data['searchsect']);
      }
    } else {
      $merger = tlocalmerger::i();
      $merger->parse($name);
    }
  }

  public static function usefile($name) {
    static::i()->check($name);
    return static::$self;
  }

  public static function inifile($class, $filename) {
    return static::inicache(litepubl::$classes->getresourcedir($class) . litepubl::$options->language . $filename);
  }

  public static function inicache($filename) {
    $self = static::i();
    if (!isset(inifiles::$files[$filename])) {
      $ini = inifiles::cache($filename);
      if (is_array($ini)) {
        $self->ini = $ini + $self->ini;
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
    static::usefile($name);
  }

  public static function getcachedir() {
    return litepubl::$paths->data . 'languages' . DIRECTORY_SEPARATOR;
  }

  public static function clearcache() {
    tfiler::delete(static::getcachedir() , false, false);
    static::i()->loaded = array();
  }

} //class
class tdateformater {
  public $date;
  public function __construct($date) {
    $this->date = $date;
  }

  public function __get($name) {
    return tlocal::translate(date($name, $this->date) , 'datetime');
  }

}