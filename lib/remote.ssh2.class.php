<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tssh2filer extends tremotefiler {
  public $sftp;
  protected $hostkey;
  protected $public_key;
  protected $private_key;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    parent::__construct();
    $this->port = 22;
    $this->ssl = false;
    $this->hostkey = false;
  }
  
  public function connect($host, $login, $password) {
    if (!parent::connect($host, $login, $password))
    return false;
    if (empty($this->port)) $this->port = 22;
    $this->handle = empty($this->key) ?
    @ssh2_connect($this->host, $this->port) :
    @ssh2_connect($this->host, $this->port, $this->hostkey);
    
    if ($this->handle) {
      $authresult = $this->public_key&& $this->private_key ?
      @ssh2_auth_pubkey_file($this->handle, $this->login, $this->public_key, $this->private_key, $this->password) :
      @ssh2_auth_password($this->handle, $this->login, $this->password);
      if ($authresult) {
        $this->sftp = ssh2_sftp($this->handle);
        $this->connected = true;
        return true;
      }
    }
    return false;
  }
  
  private function run($cmd) {
    if ($h = ssh2_exec($this->handle, $cmd)){
      stream_set_blocking( $h, true );
      stream_set_timeout( $h, $this->timeout);
      $result = stream_get_contents( $h);
      fclose( $h);
      return $result;
    }
    return false;
  }
  
  private function runbool($cmd) {
    if ($result = $this->run($cmd)) return  trim($result) != '';
    return false;
  }
  
  public function getfilename($file) {
    return "ssh2.sftp://$this->sftp/" . ltrim($filename, '/');
  }
  
  public function getfile($filename) {
    return file_get_contents($this->getfilename($filename));
  }
  
  public function putcontent($filename, $content) {
    return file_put_contents($this->getfilename($filename), $content) !== false;
  }
  
  public function upload($localfile, $filename) {
    return file_put_contents($this->getfilename($filename), file_get_contents($localfile)) !== false;
  }
  
  public function pwd() {
    if ($result = $this->run('pwd')) return rtrim(rtrim($result), '/') . '/';
    return false;
  }
  
  public function chdir($dir) {
    return $this->runbool('cd ' . $dir);
  }
  
  protected function runcommand($cmd, $filename, $mode, $recursive) {
    if ( ! $this->exists($filename) ) return false;
    if ($recursive && $this->is_dir($filename)) $cmd .= ' -R';
    return $this->runbool(sprintf('%s %o %s', $cmd, $mode, escapeshellarg($filename)));
  }
  
  public function chgrp($filename, $group, $recursive ) {
    return $this->runcommand('chgrp', $filename, $group, $recursive);
  }
  
  public function chmod($file, $mode, $recursive ) {
    $mode = $this->getmode($mode);
    return $this->runcommand('chmod', $filename, $mode, $recursive);
  }
  
  public function  chown($filename, $owner, $recursive ) {
    return $this->runcommand('chown ', $filename, $owner, $recursive);
  }
  
  public function owner($file) {
    return self::getownername(@fileowner($this->
    $file));
  }
  
  public function group($file) {
    return $this->getgroupname(@filegroup($file));
  }
  
  public function getchmod($file) {
    return @fileperms($this->getfilename($file)) & 0777;
  }
  
  public function rename($source, $destination) {
    return @ssh2_sftp_rename($this->handle, $source, $destination);
  }
  
  public function delete($file, $recursive = false) {
    if ( $this->is_file($file))  return ssh2_sftp_unlink($this->sftp, $file);
    if ( ! $recursive ) return ssh2_sftp_rmdir($this->sftp, $file);
    $filelist = $this->getdir($file);
    if ( is_array($filelist) ) {
      foreach ( $filelist as $filename => $fileinfo) {
        $this->delete($file . '/' . $filename, $recursive);
      }
    }
    return ssh2_sftp_rmdir($this->sftp, $file);
  }
  
  public function exists($file) {
    return file_exists($this->getfilename($file));
  }
  
  public function is_file($file) {
    return is_file($this->getfilename($file));
  }
  
  public function is_dir($path) {		$path = ltrim($path, '/');
    return is_dir($this->getfilename($file));
  }
  
  public function is_readable($file) {
    return is_readable($this->getfilename($file));
  }
  
  public function is_writable($file) {
    return is_writable($this->getfilename($file));
  }
  
  public function atime($file) {
    return fileatime($this->getfilename($file));
  }
  
  public function mtime($file) {
    return filemtime($this->getfilename($file));
  }
  
  public function size($file) {
    return filesize($this->getfilename($file));
  }
  
  public function mkdir($path, $chmod) {
    $path = rtrim($path, '/');
    if ( ! $chmod ) $chmod = $this->chmod_dir;
    $chmod = $this->getmode($chmod);
    return ssh2_sftp_mkdir($this->sftp, $path, $chmod, true);
  }
  
  public  function getdir($path) {
    if ( $this->is_file($path) ) 			$path = dirname($path);
    if (!  $this->is_dir($path) )  return false;
    $result = array();
    if ($dir = @dir($this->getfilename($path))) {
      while (false !== ($name = $dir->read()) ) {
        if (($name == '.') || ($name == '..') || ($name == '.svn')) continue;
        $fullname = $path.'/'.$name;
        $a = $this->getfileinfo($fullname);
        $a['name'] = $name;
        $a['isdir'] = $this->is_dir($fullname);
        $result[ $name ] = $a;
      }
      $dir->close();
      unset($dir);
      return $result;
    }
    return false;
  }
  
}//class