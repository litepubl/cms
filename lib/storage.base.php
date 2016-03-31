<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl {

class basestorage {
  public $disabled;

  public function save(tdata $obj) {
    return $this->savefile($this->getfilename($obj), $this->serialize($obj->data));
  }

  public function load(tdata $obj) {
try {
    if ($data = $this->loaddata($this->getfilename($obj))) {
$obj->data = $data + $obj->data;
return true;
    }
    } catch(\Exception $e) {
      echo 'Caught exception: ' . $e->getMessage();
}

    return false;
  }

public function getfilename(tdata $obj) {
return litepubl::$paths->data . $obj->getbasename() . '.php';
}

  public  function loadfile($filename) {
    if (self::$memcache) {
      if ($s = self::$memcache->get($filename)) return $s;
    }

    if (file_exists($filename)) {
      $s = self::uncomment_php(file_get_contents($filename));
      if (self::$memcache) self::$memcache->set($filename, $s, false, 3600);
      return $s;
    }
    return false;
  }

  public  function savetofile($base, $content) {
    if (self::$memcache) self::$memcache->set($base . '.php', $content, false, 3600);
    $tmp = $base . '.tmp.php';
    if (false === file_put_contents($tmp, self::comment_php($content))) {
      litepublisher::$options->trace(sprintf('Error write to file "%s"', $tmp));
      return false;
    }
    chmod($tmp, 0666);
    $filename = $base . '.php';
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

  public  function delete($filename) {
    if (file_exists($filename)) {
      if (!unlink($filename)) {
        chmod($filename, 0666);
        unlink($filename);
      }
    }

    if (self::$memcache) self::$memcache->delete($filename);
  }

  public  function getfile($filename) {
    if (self::$memcache) {
      if ($s = self::$memcache->get($filename)) return $s;
    }

    if (file_exists($filename)) {
      $s = file_get_contents($filename);
      if (self::$memcache) self::$memcache->set($filename, $s, false, 3600);
      return $s;
    }
    return false;
  }

  public  function setfile($filename, $content) {
    if (self::$memcache) self::$memcache->set($filename, $content, false, 3600);
    file_put_contents($filename, $content);
    @chmod($filename, 0666);
  }

  public  function savevar($filename, &$var) {
    return self::savetofile($filename, serialize($var));
  }

  public  function loadvar($filename, &$var) {
    if ($s = self::loadfile($filename . '.php')) {
      $var = unserialize($s);
      return true;
    }
    return false;
  }

  public  function comment_php($s) {
    return sprintf('<?php /* %s */ ?>', str_replace('*/', '**//*/', $s));
  }
  public  function uncomment_php($s) {
    return str_replace('**//*/', '*/', substr($s, 9, strlen($s) - 9 - 6));
  }

} //class
}