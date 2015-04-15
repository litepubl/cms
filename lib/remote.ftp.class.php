<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tftpfiler extends tremotefiler {
  public $ssl;
  private $tempfilehandle;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    parent::__construct();
    $this->timeout = 240;
    $this->ssl = false;
    $this->tempfilehandle = false;
  }
  
  public function __destruct() {
    if ( $this->handle) ftp_close($this->handle);
    if ($this->tempfilehandle) {
      @fclose($this->tempfilehandle);
      $this->tempfilehandle = false;
    }
  }
  
  public function connect($host, $login, $password) {
    if (!parent::connect($host, $login, $password)) return false;
    if (empty($this->port)) $this->port = 21;
    
    $this->handle = $this->ssl && function_exists('ftp_ssl_connect') ?
    @ftp_ssl_connect($this->host, $this->port, $this->timeout) :
    @ftp_connect($this->host, $this->port, $this->timeout);
    
    if ($this->handle && @ftp_login($this->handle,$this->login, $this->password) ) {
      @ftp_pasv( $this->handle, true );
      if ( @ftp_get_option($this->handle, FTP_TIMEOUT_SEC) < $this->timeout) {
        @ftp_set_option($this->handle, FTP_TIMEOUT_SEC, $this->timeout);
      }
      $this->connected = true;
      return true;
    }
    return false;
  }
  
  private function gettempfilehandle() {
    if (!$this->tempfilehandle) {
      $this->tempfilehandle = tmpfile();
    }
    return $this->tempfilehandle;
  }
  
  public function getfile($filename) {
    if ($temp = $this->gettempfilehandle()){
      fseek($temp, 0);
      ftruncate($temp, 0);
      if (@ftp_fget($this->handle, $temp, $filename, FTP_BINARY, $resumepos) ) {
        fseek($temp, 0);
        $result= '';
        while ( ! feof($temp) ) $result .= fread($temp, 8192);
        return $result;
      }
    }
    return false;
  }
  
  public function putcontent($filename, $content) {
    if (!($temp = $this->gettempfilehandle())) return false;
    fseek($temp, 0);
    fwrite($temp, $content);
    ftruncate($temp, strlen($content));
    fseek($temp, 0);
    $result = @ftp_fput($this->handle, $filename, $temp, FTP_BINARY);
    return $result;
  }
  
  public function upload($localfile, $filename) {
    return @ftp_put($this->handle, $filename, $localfile, FTP_BINARY);
  }
  
  public function pwd() {
    if ($result = @ftp_pwd($this->handle)) return rtrim($result, '/') . '/';
    return false;
  }
  
  public function chdir($dir) {
    return @ftp_chdir($this->handle, $dir);
  }
  
  public function chmod($file, $mode) {
    $mode = $this->getmode($mode);
    if ( ! $this->exists($file) && ! $this->is_dir($file) ) return false;
    return @ftp_chmod($this->handle, $mode, $file);
  }
  
  public function owner($file) {
    $dir = $this->getdir($file);
    return $dir[$file]['owner'];
  }
  
  public function getchmod($file) {
    if ($this->is_file($file)) {
      $dir = $this->getdir($file);
      return $dir[$file]['mode'];
    } else {
      $dir = $this->getdir(dirname($file));
      return $dir[basename($file)]['mode'];
    }
  }
  
  public function group($file) {
    $dir = $this->getdir($file);
    return $dir[$file]['group'];
  }
  
  public function rename($source, $destination) {
    return ftp_rename($this->handle, $source, $destination);
  }
  
  public function delete($file) {
    if ( empty($file) ) return false;
    if ( $this->is_file($file) ) return @ftp_delete($this->handle, $file);
    return @ftp_rmdir($this->handle, $file);
  }
  
  public function exists($file) {
    $list = @ftp_nlist($this->handle, $file);
    return !empty($list);
  }
  
  public function is_file($file) {
    return $this->exists($file) && !$this->is_dir($file);
  }
  
  public function is_dir($path) {
    if ($path == '.') return true;
    $old  = $this->pwd();
    $result = @ftp_chdir($this->handle, rtrim($path , '/') . '/' );
    $cur = $this->pwd();
    if (( $result && ($path == $cur)) || ($cur != $old)) {
      @ftp_chdir($this->handle, $old);
      return true;
    }
    return false;
  }
  
  public function mtime($file) {
    return ftp_mdtm($this->handle, $file);
  }
  
  public function size($file) {
    return ftp_size($this->handle, $file);
  }
  
  public function mkdir($path, $chmod) {
    if  ( !ftp_mkdir($this->handle, $path) ) return false;
    return parent::mkdir($path, $chmod );
  }
  
  public function rmdir($path) {
    return $this->delete($path);
  }
  
  private function perm2mode($mode) {
    $realmode = '';
    $legal =  array('', 'w', 'r', 'x', '-');
    $attarray = preg_split('//', $mode);
    
    for($i=0; $i < count($attarray); $i++)
    if($key = array_search($attarray[$i], $legal))
    $realmode .= $legal[$key];
    
    $mode = str_pad($realmode, 9, '-');
    $trans = array('-'=>'0', 'r'=>'4', 'w'=>'2', 'x'=>'1');
    $mode = strtr($mode,$trans);
    
    $newmode = '';
    $newmode .= $mode[0] + $mode[1] + $mode[2];
    $newmode .= $mode[3] + $mode[4] + $mode[5];
    $newmode .= $mode[6] + $mode[7] + $mode[8];
    return $newmode;
  }
  
  
  private function parselisting($line) {
    static $is_windows;
    if ( is_null($is_windows) ) $is_windows = strpos( strtolower(ftp_systype($this->handle)), 'win') !== false;
    if ( $is_windows && preg_match("/([0-9]\x7b2\x7d)-([0-9]\x7b2\x7d)-([0-9]\x7b2\x7d) +([0-9]\x7b2\x7d):([0-9]\x7b2\x7d)(AM|PM) +([0-9]+|<DIR>) +(.+)/", $line, $lucifer) ) {
      $b = array();
  if ( $lucifer[3] < 70 ) { $lucifer[3] +=2000; } else { $lucifer[3] += 1900; } // 4digit year fix
      $b['isdir'] = ($lucifer[7]=="<DIR>");
      if ( $b['isdir'] )
      $b['type'] = 'd';
      else
      $b['type'] = 'f';
      $b['size'] = $lucifer[7];
      $b['month'] = $lucifer[1];
      $b['day'] = $lucifer[2];
      $b['year'] = $lucifer[3];
      $b['hour'] = $lucifer[4];
      $b['minute'] = $lucifer[5];
      $b['time'] = @mktime($lucifer[4]+(strcasecmp($lucifer[6],"PM")==0?12:0),$lucifer[5],0,$lucifer[1],$lucifer[2],$lucifer[3]);
      $b['am/pm'] = $lucifer[6];
      $b['name'] = $lucifer[8];
    } else if (!$is_windows && $lucifer=preg_split("/[ ]/",$line,9,PREG_SPLIT_NO_EMPTY)) {
      
      $lcount=count($lucifer);
      if ($lcount<8) return '';
      $b = array();
    $b['isdir'] = $lucifer[0]{0} === "d";
    $b['islink'] = $lucifer[0]{0} === "l";
      if ( $b['isdir'] )
      $b['type'] = 'd';
      elseif ( $b['islink'] )
      $b['type'] = 'l';
      else
      $b['type'] = 'f';
      $b['perms'] = $lucifer[0];
      $b['number'] = $lucifer[1];
      $b['owner'] = $lucifer[2];
      $b['group'] = $lucifer[3];
      $b['size'] = $lucifer[4];
      if ($lcount==8) {
        sscanf($lucifer[5],"%d-%d-%d",$b['year'],$b['month'],$b['day']);
        sscanf($lucifer[6],"%d:%d",$b['hour'],$b['minute']);
        $b['time'] = @mktime($b['hour'],$b['minute'],0,$b['month'],$b['day'],$b['year']);
        $b['name'] = $lucifer[7];
      } else {
        $b['month'] = $lucifer[5];
        $b['day'] = $lucifer[6];
    if (preg_match("/([0-9]{2}):([0-9]{2})/",$lucifer[7],$l2)) {
          $b['year'] = date("Y");
          $b['hour'] = $l2[1];
          $b['minute'] = $l2[2];
        } else {
          $b['year'] = $lucifer[7];
          $b['hour'] = 0;
          $b['minute'] = 0;
        }
        $b['time'] = strtotime(sprintf("%d %s %d %02d:%02d",$b['day'],$b['month'],$b['year'],$b['hour'],$b['minute']));
        $b['name'] = $lucifer[8];
      }
    }
    
    return $b;
  }
  
  public function getdir($path) {
    if ( $this->is_file($path) ) $path = dirname($path) . '/';
    if (false == ($list = ftp_rawlist($this->handle, '-a ' . $path, false))) return false;
    $result = array();
    foreach ( $list as $k => $v ) {
      $a = $this->parselisting($v);
      if ( empty($a) ) continue;
      $name = $a['name'];
      if (($name == '.') || ($name == '..') || ($name == '.svn')) continue;
      $a['mode'] = octdec ($this->perm2mode($a['perms']));
      if (!isset($a['isdir'])) $a['isdir'] = $a['type'] == 'd';
      $result[ $name ] = $a;
    }
    unset($list);
    if ( count($result) == 0) return false;
    return $result;
  }
}//class