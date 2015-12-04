<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class memstorage {
  public $memcache;
  public $prefix;
  public $lifetime;
  public $table;
  public $data;
  
  public function i() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    $this->lifetime = 3600;
    $this->prefix = litepublisher::$domain . ':';
    $this->table = 'memstorage';
    $this->data = array();
    
    $this->memcache = tfilestorage::$memcache;
    if (!$this->memcache) {
      $db = litepublisher::$db;
      $res = $db->query("show tables like '$db->prefix$this->table'");
      if (!$res || !$res->num_rows) {
        $this->create_table();
        $this->created = time();
      } else {
        
      }
    }
  }
  
  public function create_table() {
    $db = litepublisher::$db;
    $db->query(
    "create table if not exists $db->prefix$this->table (
    name varchar(32) not null,
    value varchar(255),
    key (name)
    )
    ENGINE=MEMORY
    DEFAULT CHARSET=utf8
    COLLATE = utf8_general_ci");
  }
  
  public function __get($name) {
    if (strlen($name) > 32) {
      $name = md5($name);
    }
    
    if (isset($this->data[$name])) {
      return $this->data[$name];
    }
    
    return $this->get($name);
  }
  
  public function get($name) {
    $result = false;
    if ($this->memcache) {
      if ($s = $this->memcache->get($this->prefix . $name)) {
        $result = $this->unserialize($s);
      }
    } else {
      $db = litepublisher::$db;
      if ($r = $db->query("select value from $db->prefix$this->table where name = '$name' limit 1")->fetch_assoc()) {
        $result = $this->unserialize($r['value']);
      }
    }
    
    $this->data[$name] = $result;
    return $result;
  }
  
  public function __set($name, $value) {
    $exists = isset($this->data[$name]);
    $this->data[$name] = $value;
    
    if (strlen($name) > 32) {
      $name = md5($name);
    }
    
    if ($this->memcache) {
      $this->memcache->set($this->prefix . $name, $this->serialize($value), false, $this->lifetime);
    } else {
      $db = litepublisher::$db;
      $v = $db->quote($this->serialize($value));
      if ($exists) {
        $db->query("update $db->prefix$this->table set value = $v where name = '$name'");
      } else {
        $db->query("insert into $db->prefix$this->table (name, value) values('$name', $v)");
      }
    }
  }
  
  public function __unset($name) {
    if (isset($this->data[$name])) {
      unset($this->data[$name]);
    }
    
    if (strlen($name) > 32) {
      $name = md5($name);
    }
    
    if ($this->memcache) {
      $this->memcache->delete($this->prefix . $name);
    } else {
      $db = litepublisher::$db;
      $db->query("delete from $db->prefix$this->table where name = '$name' limit 1");
    }
  }
  public function serialize($data) {
    return serialize($data);
  }
  
  public function unserialize(&$data) {
    return unserialize($data);
  }
  
}//class