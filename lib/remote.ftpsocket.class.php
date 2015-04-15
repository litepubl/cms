<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tftpsocketfiler extends tremotefiler {
  private $ftp;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    parent::__construct();
    if (empty($this->port)) $this->port = 21;
    $this->timeout = 240;
  }
  
  public function __destruct() {
    if (isset($this->ftp)) {
      $this->ftp->quit();
      unset($this->ftp);
    }
  }
  
  public function connect($host, $login, $password) {
    if (!parent::connect($host, $login, $password)) return false;
    require_once(litepublisher::$paths->libinclude . 'class-ftp.php');
    $this->ftp = new ftp();
    
    $this->ftp->setTimeout($this->timeout);
    if ($this->ftp->SetServer($this->host, $this->port)&&
    $this->ftp->connect()  &&
    $this->ftp->login($this->login, $this->password) ) {
      $this->ftp->SetType(FTP_BINARY);
      $this->ftp->Passive(true);
      $this->ftp->setTimeout($this->timeout);
      $this->connected = true;
      return true;
    }
    return false;
  }
  
  public function getfile($filename) {
    if ( ! $this->exists($file) ) return false;
    if ($temp = tmpfile()){
      $result = '';
      $this->ftp->SetType(FTP_BINARY);
      if ($this->ftp->fget($temp, $file) ) {
        fseek($temp, 0); //Skip back to the start of the file being written to
        while ( ! feof($temp) ) $result .= fread($temp, 8192);
      }
      fclose($temp);
      return $result;
    }
    return false;
  }
  
  public function putcontent($filename, $content) {
    if (!($temp = tmpfile())) return false;
    fwrite($temp, $content);
    fseek($temp, 0); //Skip back to the start of the file being written to
    $this->ftp->SetType(FTP_BINARY);
    $result = $this->ftp->fput($filename, $temp);
    fclose($temp);
    return $result;
  }
  
  public function upload($localfile, $filename) {
    $this->ftp->SetType(FTP_BINARY);
    return $this->ftp->put($localfile, $filename);
  }
  
  public function pwd() {
    if ($result = $this->ftp->pwd()) return rtrim($result, '/') . '/';
    return false;
  }
  
  public function chdir($dir) {
    return $this->ftp->chdir($dir);
  }
  
  public function chmod($file, $mode) {
    $mode = $this->getmode($mode);
    return $this->ftp->chmod($file, $mode);
  }
  
  public function owner($file) {
    $dir = $this->getdir($file);
    return $dir[$file]['owner'];
  }
  
  public function getchmod($file) {
    $dir = $this->getdir($file);
    return $dir[$file]['mode'];
  }
  
  public function group($file) {
    $dir = $this->getdir($file);
    return $dir[$file]['group'];
  }
  
  public function rename($source, $destination) {
    return $this->ftp->rename($source, $destination);
  }
  
  public function delete($file, $recursive = false ) {
    if ( empty($file) ) return false;
    if ( $this->is_file($file) ) return $this->ftp->delete($file);
    return $this->ftp->rmdir($file);
    
    //return $this->ftp->mdel($file);
  }
  
  public function exists($file) {
    return $this->ftp->is_exists($file);
  }
  
  public function is_file($file) {
    return !$this->is_dir($file);
  }
  
  public function is_dir($path) {
    $pwd = $this->pwd();
    if ( $this->chdir($path) ) {
      $this->chdir($pwd);
      return true;
    }
    return false;
  }
  
  public function mtime($file) {
    return $this->ftp->mdtm($file);
  }
  
  public function size($file) {
    return $this->ftp->filesize($file);
  }
  
  public function mkdir($path, $chmod) {
    if ( ! $this->ftp->mkdir($path) ) return false;
    return parent::mkdir($path, $chmod);
  }
  
  public function rmdir($path) {
    return $this->ftp->rmdir($path);
    //		return $this->ftp->mdel($path);
  }
  
  public function getdir($path) {
    if ( $this->is_file($path) ) $path = dirname($path) . '/';
    if (($list = $this->ftp->dirlist($path)) === false) return false;
    $result = array();
    foreach ( $list as $a) {
      $name = $a['name'];
      if (($name == '.') || ($name == '..') || ($name == '.svn')) continue;
      $a['mode'] = octdec ($this->perm2mode($a['perms']));
      if (!isset($a['isdir'])) $a['isdir'] = $a['type'] == 'd';
      $result[ $name ] = $a;
    }
    return $result;
  }
  
}//class