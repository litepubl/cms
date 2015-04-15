<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlocalfiler extends tremotefiler {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    parent::__construct();
    $this->connected = true;
  }
  
  public function getfile($file) {
    return file_get_contents($file);
  }
  
  public function putcontent($filename, $content) {
    return file_put_contents($filename, $content);
  }
  
  public function upload($localfile, $filename) {
    return file_put_contents($filename, file_get_contents($localfile)) !== false;
  }
  
  public function pwd() {
    return getcwd();
  }
  
  public function chdir($dir) {
    //echo "chdir $dir\n";
    return chdir($dir);
  }
  
  public function chgrp($file, $group, $recursive = false) {
    if ( ! $this->exists($file) ) return false;
    if ( ! $recursive  || ! $this->is_dir($file) ) return @chgrp($file, $group);
    
    $file = rtrim($file, '/') . '/';
    $filelist = $this->getdir($file);
    foreach ($filelist as $filename) {
      $this->chgrp($file . $filename, $group, $recursive);
    }
    return true;
  }
  
  public function chmod($file, $mode ) {
    return @chmod($file, $this->getmode($mode));
  }
  
  public function chown($file, $owner, $recursive = false) {
    if ( ! $this->exists($file) ) return false;
    if ( ! $recursive  || ! $this->is_dir($file) ) return @chown($file, $owner);
    
    $filelist = $this->getdir($file);
    foreach ($filelist as $filename) {
      $this->chown($file . '/' . $filename, $owner, $recursive);
    }
    return true;
  }
  
  public function owner($file) {
    return self::getownername(@fileowner($file));
  }
  
  public function getchmod($file) {
    return substr(decoct(@fileperms($file)),3);
  }
  
  public function group($file) {
    return $this->getgroupname(@filegroup($file));
  }
  
  public function rename($source, $destination) {
    return @rename($source, $destination);
  }
  
  public function delete($file, $recursive = false) {
    if ( empty($file)) return false;
    $file = str_replace('\\', '/', $file); //for win32, occasional problems deleteing files otherwise
    if ( $this->is_file($file) ) return unlink($file);
    if ( ! $recursive && $this->is_dir($file) ) return rmdir($file);
    
    $result = true;
    if ($filelist = $this->getdir(rtrim($file, '/') . '/', true)) {
      foreach ($filelist as $filename => $fileinfo) {
        $result = $this->delete($file . $filename, true ) && $result;
      }
    }
    if ( file_exists($file) && ! @rmdir($file) ) return  false;
    return $result;
  }
  
  public function exists($file) {
    return file_exists($file);
  }
  
  public function is_file($file) {
    return is_file($file);
  }
  
  public function is_dir($path) {
    return is_dir($path);
  }
  
  public function is_readable($file) {
    return is_readable($file);
  }
  
  public function is_writable($file) {
    return is_writable($file);
  }
  
  public function atime($file) {
    return fileatime($file);
  }
  
  public function mtime($file) {
    return filemtime($file);
  }
  
  public function size($file) {
    return filesize($file);
  }
  
  public function mkdir($path, $chmod) {
    if ( ! $chmod) $chmod = $this->chmod_dir;
    $chmod = $this->getmode($chmod);
    if ( ! @mkdir($path, $chmod) ) return false;
    @chmod($path, $chmod);
    return true;
  }
  
  public function getdir($path) {
    if ( $this->is_file($path) ) $path = dirname($path);
    $path = str_replace('/', DIRECTORY_SEPARATOR  , $path);
    $path = rtrim($path, DIRECTORY_SEPARATOR);
    if ($dir = @dir($path)) {
      $result = array();
      while (false !== ($name= $dir->read()) ) {
        if (($name == '.') || ($name == '..') || ($name == '.svn')) continue;
        $fullname = $path.DIRECTORY_SEPARATOR  . $name;
        $a = $this->getfileinfo($fullname);
        $a['name'] = $name;
        $result[$name] = $a;
      }
      $dir->close();
      unset($dir);
      return $result;
    }
    return false;
  }
  
  public function forcedir($dir) {
    $dir = str_replace('/', DIRECTORY_SEPARATOR  , $dir);
    $dir = rtrim($dir, DIRECTORY_SEPARATOR);
    if (!is_dir($dir)) {
      $this->forcedir(dirname($dir));
      $this->mkdir($dir, $this->chmod_dir);
    }
  }
  
}//class