<?php
class tableprop {
  public $callbacks;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    $this->callbacks = array();
  }
  
  public function addprop($callback) {
    $this->callbacks[] = $callback;
    $id = count($this->callbacks) -  1;
    return 'prop' . $id;
  }
  
  public function __get($name) {
    $id = (int) substr($name, strlen('prop'));
    return call_user_func_array($this->callbacks[$id], array(ttheme::$vars['item']));
  }
  
}