<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tpoolitems extends tdata {
  protected $perpool;
  protected $pool;
  protected $modified;
  protected $ongetitem;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'poolitems';
    $this->perpool = 20;
    $this->pool = array();
    $this->modified = array();
  }
  
  public function getitem($id) {
    if (isset($this->ongetitem)) {
      return call_user_func_array($this->ongetitem, array($id));
    }
    
    $this->error('Call abastract method getitem in class' . get_class($this));
  }
  
  public function getfilename($idpool) {
    return $this->basename . '.pool.' . $idpool;
  }
  
  public function loadpool($idpool) {
    if ($data = litepublisher::$urlmap->cache->get($this->getfilename($idpool))) {
      $this->pool[$idpool] = $data;
    } else {
      $this->pool[$idpool] = array();
    }
  }
  
  public function savepool($idpool) {
    if (!isset($this->modified[$idpool])) {
      litepublisher::$urlmap->onclose = array($this, 'savemodified', $idpool);
      $this->modified[$idpool] = true;
    }
  }
  
  public function savemodified($idpool) {
    litepublisher::$urlmap->cache->set($this->getfilename($idpool), $this->pool[$idpool]);
  }
  
  public function getidpool($id) {
    $idpool = (int) floor ($id /$this->perpool);
    if (!isset($this->pool[$idpool])) $this->loadpool($idpool);
    return $idpool;
  }
  
  public function get($id) {
    $idpool = $this->getidpool($id);
    if (isset($this->pool[$idpool][$id])) return $this->pool[$idpool][$id];
    $result = $this->getitem($id);
    $this->pool[$idpool][$id] = $result;
    $this->savepool($idpool);
    return $result;
  }
  
  public function set($id, $item) {
    $idpool = $this->getidpool($id);
    $this->pool[$idpool][$id] = $item;
    $this->savepool($idpool);
  }
  
}//class