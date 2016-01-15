<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class memstorage {
  public $memcache;
  public $memcache_prefix;
  public $lifetime;
  public $table;
  public $data;
  private $table_checked;

  public static function i() {
    return getinstance(__class__);
  }

  public function __construct() {
    $this->memcache_prefix = litepublisher::$domain . ':';
    $this->table = 'memstorage';
    $this->table_checked = false;
    $this->data = array();
    if ($this->memcache = tfilestorage::$memcache) {
      $this->lifetime = 3600;
    } else {
      $this->lifetime = 10800;
    }
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
      if ($s = $this->memcache->get($this->memcache_prefix . $name)) {
        $result = $this->unserialize($s);
        $this->data[$name] = $result;
      }
    } else {
      if (!$this->table_checked) {
        $this->check();
      }

      $db = litepublisher::$db;
      if ($r = $db->query("select value from $db->prefix$this->table where name = '$name' limit 1")->fetch_assoc()) {
        $result = $this->unserialize($r['value']);
        $this->data[$name] = $result;
      }
    }

    return $result;
  }

  public function __set($name, $value) {
    if (strlen($name) > 32) {
      $name = md5($name);
    }

    $exists = isset($this->data[$name]);
    $this->data[$name] = $value;

    if ($this->memcache) {
      $this->memcache->set($this->memcache_prefix . $name, $this->serialize($value) , false, $this->lifetime);
    } else {
      if (!$this->table_checked) {
        $this->check();
      }

      $db = litepublisher::$db;
      $v = $db->quote($this->serialize($value));
      if ($exists) {
        $db->query("update $db->prefix$this->table set value = $v where name = '$name' limit 1");
      } else {
        $db->query("insert into $db->prefix$this->table (name, value) values('$name', $v)");
      }
    }
  }

  public function __unset($name) {
    if (strlen($name) > 32) {
      $name = md5($name);
    }

    if (isset($this->data[$name])) {
      unset($this->data[$name]);
    }

    if ($this->memcache) {
      $this->memcache->delete($this->memcache_prefix . $name);
    } else {
      if (!$this->table_checked) {
        $this->check();
      }

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

  public function check() {
    $this->table_checked = true;

    //exclude throw exception
    $db = litepublisher::$db;
    $res = $db->mysqli->query("select value from $db->prefix$this->table where name = 'created' limit 1");
    if (is_object($res) && ($r = $res->fetch_assoc())) {
      $res->close();
      $created = $this->unserialize($r['value']);
      if ($created + $this->lifetime < time()) {
        $this->loadall();
        $this->clear_table();
        $this->data['created'] = time();
        $this->saveall();
      }
    } else {
      $this->create_table();
      $this->created = time();
    }
  }

  public function loadall() {
    $db = litepublisher::$db;
    $res = $db->query("select * from $db->prefix$this->table");
    if (is_object($res)) {
      while ($item = $res->fetch_assoc()) {
        $this->data[$item['name']] = $this->unserialize($item['value']);
      }
    }
  }

  public function saveall() {
    $db = litepublisher::$db;
    $a = array();
    foreach ($this->data as $name => $value) {
      $a[] = sprintf('(\'%s\',%s)', $name, $db->quote($this->serialize($value)));
    }

    $values = implode(',', $a);
    $db->query("insert into $db->prefix$this->table (name, value) values $values");
  }

  public function create_table() {
    $db = litepublisher::$db;
    $db->mysqli->query("create table if not exists $db->prefix$this->table (
    name varchar(32) not null,
    value varchar(255),
    key (name)
    )
    ENGINE=MEMORY
    DEFAULT CHARSET=utf8
    COLLATE = utf8_general_ci");
  }

  public function clear_table() {
    $db = litepublisher::$db;
    try {
      $db->query("truncate table $db->prefix$this->table");
    }
    catch(Exception $e) {
      //silince
      
    }
  }

} //class