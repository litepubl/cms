<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class inifiles {
  public static $files = array();

  public static function cache($filename) {
    if (isset(self::$files[$filename])) {
      return self::$inifiles[$filename];
    }

    $datafile = tlocal::getcachedir() . 'cacheini.' . md5($filename);
    $ini = litepubl::$storage->loaddata($datafile);
    if (!is_array($ini)) {
      if (file_exists($filename)) {
        $ini = parse_ini_file($filename, true);
        litepubl::$storage->savedata($datafile, $ini);
      } else {
        $ini = array();
      }
    }

    if (!isset(self::$files)) self::$files = array();
    self::$files[$filename] = $ini;
    return $ini;
  }

  public static function getresource($class, $filename) {
    $dir = litepublisher::$classes->getresourcedir($class);
    return self::cache($dir . $filename);
  }

}