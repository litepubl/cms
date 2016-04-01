<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tarray2prop {
  public $array;
  public function __construct(array $a = null) {
    $this->array = $a;
  }
  public function __get($name) {
    return $this->array[$name];
  }
  public function __set($name, $value) {
    $this->array[$name] = $value;
  }
  public function __isset($name) {
    return array_key_exists($name, $this->array);
  }
  public function __tostring() {
    return $this->array[''];
  }
} //class