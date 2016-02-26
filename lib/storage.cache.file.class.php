<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
class cachestorage_file {

  public function getdir() {
    return litepublisher::$paths->cache;
  }

  public function set($filename, $data) {
    $fn = $this->getdir() . $filename;
    file_put_contents($fn, serialize($data));
    @chmod($fn, 0666);
  }

  public function get($filename) {
    $fn = $this->getdir() . $filename;
    if (file_exists($fn) && ($s = file_get_contents($fn))) {
      return unserialize($s);
    }

    return false;
  }

  public function delete($filename) {
    $fn = $this->getdir() . $filename;
    if (file_exists($fn)) {
      unlink($fn);
    }
  }

  public function exists($filename) {
    return file_exists($this->getdir() . $filename);
  }

  public function clear() {
    $path = $this->getdir();
    if ($h = @opendir($path)) {
      while (FALSE !== ($filename = @readdir($h))) {
        if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) continue;
        $file = $path . $filename;
        if (is_dir($file)) {
          tfiler::delete($file . DIRECTORY_SEPARATOR, true, true);
        } else {
          unlink($file);
        }
      }
      closedir($h);
    }
  }

} //class