<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl {

  class memcachestorage extends storage {
    public $memcache;

    public function __construct() {
      $this->memcache = litepubl::$memcache;
    }

    public function loadfile($filename) {
      if ($s = $this->memcache->get($filename)) {
        return $s;
      }

      if ($s = parent::loadfile($filename)) {
        $this->memcache->set($filename, $s, false, 3600);
        return $s;
      }

      return false;
    }

    public function savefile($filename, $content) {
      $this->memcache->set($filename, $content, false, 3600);
      return parent::savefile($filename, $content);
    }

    public function delete($filename) {
      parent::delete($filename);
      $this->memcache->delete($filename);
    }

  } //class
  
} //namespace