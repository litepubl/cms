<?php

class getter {
public $get;
public $set;

public function __construct($get = null, $set = null) {
$this->get = $get;
$this->set = $set;
}

public function __get($name) {
return call_user_func_array($this->get, array($name));
}

public function __set($name, $value) {
call_user_func_array($this->set, array($name, $value));
}

}