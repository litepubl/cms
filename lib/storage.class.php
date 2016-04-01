<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl {
  class datastorage {
    public $data;
    private $modified;

    public function __construct() {
      $this->data = [];
    }

    public function getStorage() {
      return litepubl::$storage;
    }

    public function save(tdata $obj) {
      $this->modified = true;
      $base = $obj->getbasename();
      if (!isset($this->data[$base])) {
        $this->data[$base] = & $obj->data;
      }

      return true;
    }

    public function load(tdata $obj) {
      $base = $obj->getbasename();
      if (isset($this->data[$base])) {
        $obj->data = & $this->data[$base];
        return true;
      } else {
        $this->data[$base] = & $obj->data;
        return false;
      }
    }

    public function remove(tdata $obj) {
      $base = $obj->getbasename();
      if (isset($this->data[$base])) {
        unset($this->data[$base]);
        $this->modified = true;
        return true;
      }
    }

    public function loaddata() {
      if ($data = $this->getStorage()->loaddata(litepubl::$paths->data . 'storage')) {
        $this->data = $data;
        return true;
      }

      return false;
    }

    public function saveModified() {
      if (!$this->modified) {
        return false;
      }

      $lockfile = litepubl::$paths->data . 'storage.lok';
      if (($fh = @\fopen($lockfile, 'w')) && \flock($fh, LOCK_EX | LOCK_NB)) {
        $this->getStorage()->savedata(litepubl::$paths->data . 'storage', $this->data);
        $this->modified = false;
        \flock($fh, LOCK_UN);
        \fclose($fh);
        @\chmod($lockfile, 0666);
        return true;
      } else {
        if ($fh) {
          @\fclose($fh);
        }

        $this->error('Storage locked, data not saved');
        return false;
      }
    }

    public function error($mesg) {
      tfiler::log($mesg);
    }

    public function isInstalled() {
      return count($this->data);
    }

  } //class
  
} //namespace