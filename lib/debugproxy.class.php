<?php

class tdebugproxy {
  public static $trace;
  public static $total;
  public static $stat;
  public static $counts;
  public $obj;
  public $data;
  public $items;
  public $templates;
  
  public function __construct($obj) {
    $this->obj = $obj;
    if (isset($obj->data)) $this->data = &$obj->data;
    if ($obj instanceof titems) $this->items = &$obj->items;
    if ($obj instanceof ttheme) $this->templates = &$obj->templates;
  }
  
  public function __isset($name) {
    return $this->obj->__isset($name);
  }
  
  public function __get($name) {
    $m = microtime(true);
    $r = $this->obj->$name;
    $this->addstat(" get $name", microtime(true) - $m);
    return $r;
  }
  
  public function __set($name, $value) {
    $m = microtime(true);
    $this->obj->$name = $value;
    $this->addstat(" set $name", microtime(true) - $m);
  }
  
  public function __call($name, $args) {
    //echo get_class($this->obj), " call $name<br>";
    $m = microtime(true);
    $r = call_user_func_array(array($this->obj, $name), $args);
    $this->addstat(" call $name", microtime(true) - $m);
    return $r;
  }
  
  public function addstat($s, $time) {
    $name = get_class($this->obj) . $s;
    //echo "$name<br>";
    self::$trace[] = array($name, $time);
    if (isset(self::$total[$name])) {
      self::$total[$name] += $time;
      ++self::$counts[$name];
    } else {
      self::$total[$name] = $time;
      self::$counts[$name] = 1;
    }
  }
  
  public static function showperformance() {
    echo "<pre>\n";
    arsort(self::$total);
    $total = 0;
    foreach (self::$total as $k => $v) {
      $total += $v;
      $v= round($v * 1000, 4);
      //$v = round($v * 100000);
      echo self::$counts[$k];
      echo " $k $v\n";
    }
    $total = $total * 1000;
    echo "total $total\n";
  }
  
}