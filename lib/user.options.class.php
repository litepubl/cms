<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tuseroptions extends titems {
  public $defvalues;
  private $defitems;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->basename = 'usersoptions';
    $this->table = 'useroptions';
    $this->addmap('defvalues', array());
    $this->defitems = array();
  }
  
  public function getval($name) {
    return$this->getvalue(litepublisher::$options->user, $name);
  }
  
  public function setval($name, $value) {
    return $this->setvalue(litepublisher::$options->user, $name, $value);
  }
  
  public function getitem($id) {
    $id = (int) $id;
    if (isset($this->items[$id]) || $this->select("$this->thistable.id = $id", 'limit 1'))     return $this->items[$id];
    
    $item = $this->defvalues;
    $item['id'] = $id;
    $this->items[$id] = $item;
    $this->defitems[] = $id;
    return $item;
  }
  
  public function getvalue($id, $name) {
    $item = $this->getitem($id);
    return $item[$name];
  }
  
  public function setvalue($id, $name, $value) {
    $id = (int) $id;
    $item = $this->getitem($id);
    if ($value === $item[$name]) return;
    $this->items[$id][$name] = $value;
    $item[$name] = $value;
    $item['id'] = $id;
    $this->setitem($item);
  }
  
  public function setitem($item) {
    $this->items[(int) $item['id']] = $item;
    $i = array_search($item['id'], $this->defitems);
    if ($i === false) {
      $this->db->updateassoc($item);
    } else {
      $this->db->insert($item);
      array_splice($this->defitems, $i, 1);
    }
  }
  
}//class