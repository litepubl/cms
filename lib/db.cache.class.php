<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class dbcache extends cachestorage_memcache {

  public function set($sql, $res) {
    if (is_object($res)) {
      if ($res->num_rows > 1000) return;
      $cache_result = new dbcache_result($res);
      parent::set(md5($sql) , $cache_result->data);
      $res->data_seek(0);
    }
  }

  public function get($sql) {
    if ($data = parent::get(md5($sql))) {
      return new dbcache_result($data);
    }

    return false;
  }

} //class
//MySQLi_Result
class dbcache_result implements Iterator {
  //public $current_field ;
  public $field_count;
  //public $lengths;
  public $num_rows;
  public $data;
  protected $items;
  protected $keys;
  protected $index;

  public function __construct($res) {
    $this->index = 0;
    $this->items = array();
    $this->keys = array();

    $this->data = array(
      'items' => & $this->items,
      'keys' => & $this->keys,
    );

    if (is_array($res)) {
      $this->items = $res['items'];
      $this->keys = $res['keys'];
    } elseif (is_object($res)) {
      $this->items = $res->fetch_all(MYSQLI_NUM);

      while ($finfo = $res->fetch_field()) {
        $this->keys[] = $finfo->name;
      }
    }

    $this->num_rows = count($this->items);
    $this->field_count = count($this->keys);
  }

  public function data_seek($offset) {
    if ($offset >= 0 && $offset < count($this->items)) {
      $this->index = $offset;
      return true;
    }

    return false;
  }

  public function fetch_all($resulttype = MYSQLI_NUM) {
    switch (resulttype) {
      case MYSQLI_NUM:
        return $this->items;

      case MYSQLI_ASSOC:
        $result = array();
        foreach ($this->items as $item) {
          $result[] = array_combine($this->keys, $item);
        }

        return $result;

      case MYSQLI_BOTH:
        $result = array();
        foreach ($this->items as $item) {
          $result[] = array_merge($item, array_combine($this->keys, $item));
        }

        return $result;
    }
  }

  public function fetch_array($resulttype = MYSQLI_BOTH) {
    switch (resulttype) {
      case MYSQLI_ASSOC:
        return $this->fetch_assoc();

      case MYSQLI_NUM:
        return $this->fetch_row();

      case MYSQLI_BOTH:
        if ($this->index < 0 || $this->index >= count($this->items)) return false;
        $item = $this->items[$this->index++];
        return array_merge($item, array_combine($this->keys, $item));
      }
  }

  public function fetch_assoc() {
    if ($this->index < 0 || $this->index >= count($this->items)) return false;
    return array_combine($this->keys, $this->items[$this->index++]);
  }

  /*
  public function fetch_field_direct ( int $fieldnr ) {}
  public function fetch_field () {}
  public function fetch_fields () {}
  */

  public function fetch_object($class_name = 'stdClass', $params = false) {
    if ($this->index < 0 || $this->index >= count($this->items)) return false;
    $result = new $class_name($params);
    $item = $this->items[$this->index++];
    foreach ($item as $i => $v) {
      $result->{$this->keys[$i]} = $v;
    }

    return $result;
  }

  public function fetch_row() {
    if ($this->index < 0 || $this->index >= count($this->items)) return false;
    return $this->items[$this->index++];
  }

  //public function field_seek ( int $fieldnr ) {}
  public function free() {
    $this->items = array();
    $this->keys = array();
    $this->index = 0;
  }

  public function close() {
    $this->free();
  }

  //iterator
  public function rewind() {
    $this->index = 0;
  }

  public function current() {
    return $this->items[$this->index];
  }

  public function key() {
    return $this->index;
  }

  public function next() {
    ++$this->index;
  }

  public function valid() {
    return isset($this->items[$this->index]);
  }

} //class