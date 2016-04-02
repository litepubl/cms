<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class cachestorage_memcache {
  public $memcache;
  public $lifetime;
  public $prefix;
  public $revision;
  public $revisionKey;

  public function __construct() {
    $this->memcache = litepubl::$memcache;
    $this->lifetime = 3600;
    $this->prefix = litepubl::$domain . ':cache:';
    $this->revision = 0;
    $this->revisionKey = 'cache_revision';
if ($this->memcache) {
    $this->getRevision();
}
  }

  public function getPrefix() {
    return $this->prefix . $this->revision . '.';
  }

  public function getRevision() {
    return $this->revision = (int)$this->memcache->get($this->prefix . $this->revisionKey);
  }

  public function clear() {
    $this->revision++;
    $this->memcache->set($this->prefix . $this->revisionKey, "$this->revision", false, $this->lifetime);
  }

  public function serialize($data) {
    return serialize($data);
  }

  public function unserialize(&$data) {
    return unserialize($data);
  }

  public function setString($filename, $str) {
    $this->memcache->set($this->getPrefix() . $filename, $str, false, $this->lifetime);
  }

  public function set($filename, $data) {
    $this->setString($filename, $this->serialize($data));
  }

  public function getString($filename) {
    return $this->memcache->get($this->getPrefix() . $filename);
  }

  public function get($filename) {
    if ($s = $this->getString($filename)) {
      return $this->unserialize($s);
    }

    return false;
  }

  public function delete($filename) {
    $this->memcache->delete($this->getPrefix() . $filename);
  }

  public function exists($filename) {
    return !!$this->memcache->get($this->prefix . $filename);
  }

} //class