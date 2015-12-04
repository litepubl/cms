<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class cachestorage_memcache {
  public $prefix;
  public $memcache;
  public $lifetime;
  public $revision;
  public $revision_key;
  
  public function __construct($memcache) {
    $this->prefix = litepublisher::$domain . ':cache:';
    $this->memcache = $memcache;
    $this->lifetime = 3600;
    $this->revision = 0;
    $this->revision_key = 'cache_revision';
    $this->getrevision();
  }
  
  public function getrevision() {
    return $this->revision = (int) $this->memcache->get($this->prefix . $this->revision_key);
  }
  
  public function clear() {
    $this->revision++;
    $this->memcache->set($this->prefix . $this->revision_key, "$this->revision", false, $this->lifetime);
  }
  
  public function serialize($data) {
    return serialize($data);
  }
  
  public function unserialize(&$data) {
    return unserialize($data);
  }
  
  public function set($filename, $data) {
    $this->memcache->set($this->prefix . $filename,$this->serialize(array(
    'revision' => $this->revision,
    //'time' => time(),
    'data' => $data
    )), false, $this->lifetime);
  }
  
  public function get($filename) {
    if ($s = $this->memcache->get($this->prefix . $filename)) {
      $a = $this->unserialize($s);
      if ($a['revision'] == $this->revision) {
        return $a['data'];
      } else {
        $this->memcache->delete($this->prefix . $filename);
      }
    }
    
    return false;
  }
  
  public function delete($filename) {
    $this->memcache->delete($this->prefix . $filename);
  }
  
  public function exists($filename) {
    return !!$this->memcache->get($this->prefix . $filename);
  }
  
}//class