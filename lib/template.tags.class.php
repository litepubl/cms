<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttemplatetags {
  private $items;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    $template = ttemplate::i();
    $this->items = &$template->data['tags'];
  }
  
  public function save() {
    $template = ttemplate::i();
    $template->save();
  }
  
  public function add($name, $class, $func) {
    $this->items[$name] = array(
    'class' => $classname,
    'func' => $func
    );
    $this->save();
  }
  
  public function delete($name) {
    if (isset($this->items[$name])) {
      unset($this->items[$name]);
      $this->save();
    }
  }
  
  public function deleteclass($class) {
    foreach ($this->items as$name => $item) {
      if ($item['class'] == $class) unset($this->items[$name]);
    }
    $this->save();
  }
  
  public function __get($name) {
    if (!isset($this->items[$name]))  return '';
    $func = $this->items[$name]['func'];
    $class = $this->tags[$name]['class'];
    if (empty($class)) {
      if (function_exists($func)){
        return $func($name);
      } else {
        $this->delete($name);
      }
    } elseif (class_exists($class)){
      $obj = getinstance($class);
      return  $obj->$func($name);
    } else {
      $this->delete($name);
    }
  }
  
}//class