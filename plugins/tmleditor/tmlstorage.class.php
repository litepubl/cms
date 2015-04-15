<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmlstorage extends tplugin {
  public $items;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addmap('items', array());
  }
  
  public function classadded($classname) {
    $obj = getinstance($classname);
    if (isset($obj->data['tml'])) {
      $this->items[] = $classname;
      $this->save();
    }
  }
  
  public function classdeleted($classname) {
    if (false !== ($i = array_search($classname, $this->items)) {
      array_splice($this->items, $i, 1);
      $this->save();
    }
  }
  
}//class