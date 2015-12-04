<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tfilestorage {
  public static $disabled;
  public static $memcache = false;
  
  public static function save(tdata $obj) {
    if (self::$disabled) return false;
    return self::savetofile(litepublisher::$paths->data .$obj->getbasename(), $obj->savetostring());
  }
  
  public static function load(tdata $obj) {
    if ($s = self::loadfile(litepublisher::$paths->data . $obj->getbasename() .'.php')) {
      return $obj->loadfromstring($s);
    }
    return false;
  }
  
  public static function loadfile($filename) {
    if (self::$memcache) {
      if ($s =  self::$memcache->get($filename)) return $s;
    }
    
    if (file_exists($filename)) {
      $s = self::uncomment_php(file_get_contents($filename));
      if (self::$memcache) self::$memcache->set($filename, $s, false, 3600);
      return $s;
    }
    return false;
  }
  
  public static function savetofile($base, $content) {
    if (self::$memcache) self::$memcache->set($base . '.php', $content, false, 3600);
    $tmp = $base .'.tmp.php';
    if(false === file_put_contents($tmp, self::comment_php($content))) {
      litepublisher::$options->trace(sprintf('Error write to file "%s"', $tmp));
      return false;
    }
    chmod($tmp, 0666);
    $filename = $base .'.php';
    if (file_exists($filename)) {
      $back = $base . '.bak.php';
      self::delete($back);
      rename($filename, $back);
    }
    if (!rename($tmp, $filename)) {
      litepublisher::$options->trace(sprintf('Error rename temp file "%s" to "%s"', $tmp, $filename));
      return false;
    }
    return true;
  }
  
  public static function delete($filename) {
    if (file_exists($filename)) {
      if (!unlink($filename)) {
        chmod($filename, 0666);
        unlink($filename);
      }
    }
    
    if (self::$memcache) self::$memcache->delete($filename);
  }
  
  public static function getfile($filename) {
    if (self::$memcache) {
      if ($s =  self::$memcache->get($filename)) return $s;
    }
    
    if (file_exists($filename)) {
      $s = file_get_contents($filename);
      if (self::$memcache) self::$memcache->set($filename, $s, false, 3600);
      return $s;
    }
    return false;
  }
  
  public static function setfile($filename, $content) {
    if (self::$memcache) self::$memcache->set($filename, $content, false, 3600);
    file_put_contents($filename, $content);
    @chmod($filename, 0666);
  }
  
  public static function savevar($filename, &$var) {
    return self::savetofile($filename, serialize($var));
  }
  
  public static function loadvar($filename, &$var) {
    if ($s = self::loadfile($filename . '.php')) {
      $var = unserialize($s);
      return true;
    }
    return false;
  }
  
  public static function comment_php($s) {
    return sprintf('<?php /* %s */ ?>', str_replace('*/', '**//*/', $s));
  }
  
  public static function uncomment_php($s) {
    return str_replace('**//*/', '*/', substr($s, 9, strlen($s) - 9 - 6));
  }
  
}//class