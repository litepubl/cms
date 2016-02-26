<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tstorage extends tfilestorage {
  public static $data;
  private static $modified;

  public static function save(tdata $obj) {
    self::$modified = true;
    $base = $obj->getbasename();
    if (!isset(self::$data[$base])) self::$data[$base] = & $obj->data;
    return true;
  }

  public static function load(tdata $obj) {
    $base = $obj->getbasename();
    if (isset(self::$data[$base])) {
      $obj->data = & self::$data[$base];
      $obj->afterload();
      return true;
    } else {
      self::$data[$base] = & $obj->data;
      return false;
    }
  }

  public static function remove(tdata $obj) {
    $base = $obj->getbasename();
    if (isset(self::$data[$base])) {
      unset(self::$data[$base]);
      self::$modified = true;
      return true;
    }
  }

  public static function savemodified() {
    if (self::$modified) {
      if (self::$disabled) return false;
      $lock = litepublisher::$paths->data . 'storage.lok';
      if (($fh = @fopen($lock, 'w')) && flock($fh, LOCK_EX | LOCK_NB)) {
        self::savetofile(litepublisher::$paths->data . 'storage', serialize(self::$data));
        flock($fh, LOCK_UN);
        fclose($fh);
        @chmod($lock, 0666);
      } else {
        tfiler::log('Storage locked, data not saved');
      }
      self::$modified = false;
      return true;
    }
    return false;
  }

  public static function loaddata() {
    self::$data = array();
    return self::loadvar(litepublisher::$paths->data . 'storage', self::$data);
  }

} //class