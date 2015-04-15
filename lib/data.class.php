<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdata {
  const zerodate = '0000-00-00 00:00:00';
  public $data;
  public $basename;
  public $cache;
  public $coclasses;
  public $coinstances;
  public $lockcount;
  public $table;
  public static $guid = 0;
  
  public static function i() {
    return getinstance(get_called_class());
  }
  
  public static function instance() {
    return getinstance(get_called_class());
  }
  
  public function __construct() {
    $this->lockcount = 0;
    $this->cache= true;
    $this->data= array();
    $this->coinstances = array();
    $this->coclasses = array();
    $this->basename = substr(get_class($this), 1);
    $this->create();
  }
  
  protected function create() {
  }
  
  public function __get($name) {
    if (method_exists($this, $get = 'get' . $name))  {
      return $this->$get();
    } elseif (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    } else {
      foreach ($this->coinstances as $coinstance) {
        if (isset($coinstance->$name)) return $coinstance->$name;
      }
      return    $this->error(sprintf('The requested property "%s" not found in class  %s', $name, get_class($this)));
    }
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = 'set' . $name)) {
      $this->$set($value);
      return true;
    }
    
    if (key_exists($name, $this->data)) {
      $this->data[$name] = $value;
      return true;
    }
    
    foreach ($this->coinstances as $coinstance) {
      if (isset($coinstance->$name)) {
        $coinstance->$name = $value;
        return true;
      }
    }
    
    return false;
  }
  
  public  function __call($name, $params) {
    if (method_exists($this, strtolower($name))) {
      return call_user_func_array(array($this, strtolower($name)), $params);
    }
    
    foreach ($this->coinstances as $coinstance) {
      if (method_exists($coinstance, $name) || $coinstance->method_exists($name))
      return call_user_func_array(array($coinstance, $name), $params);
    }
    $this->error("The requested method $name not found in class " . get_class($this));
  }
  
  public function __isset($name) {
    if (array_key_exists($name, $this->data) || method_exists($this, "get$name") || method_exists($this, "Get$name")) return true;
    foreach ($this->coinstances as $coinstance) {
      if (isset($coinstance->$name)) return true;
    }
    return false;
  }
  
  public function method_exists($name) {
    return false;
  }
  
  public function error($Msg, $code = 0) {
    throw new Exception($Msg, $code);
  }
  
  public function getbasename() {
    return $this->basename;
  }
  
  public function install() {
    $this->externalchain('Install');
  }
  
  public function uninstall() {
    $this->externalchain('Uninstall');
  }
  
  public function validate($repair = false) {
    $this->externalchain('Validate', $repair);
  }
  
  protected function externalchain($func, $arg = null) {
    $parents = class_parents($this);
    array_splice($parents, 0, 0, get_class($this));
    foreach ($parents as $key => $class) {
      $this->externalfunc($class, $func, $arg);
    }
  }
  
  public function externalfunc($class, $func, $args) {
    if ($filename = litepublisher::$classes->getclassfilename($class, true)) {
      $externalname = basename($filename, '.php') . '.install.php';
      $dir = dirname($filename) . DIRECTORY_SEPARATOR;
      $file = $dir . 'install' . DIRECTORY_SEPARATOR . $externalname;
      if (!file_exists($file)) {
        $file =$dir .  $externalname;
        if (!file_exists($file)) return;
      }
      include_once($file);
      $fnc = $class . $func;
      if (function_exists($fnc)) {
        //$fnc($this, $arg);
        if (is_array($args)) {
          array_unshift($args, $this);
        } else {
          $args = array($this, $args);
        }
        return call_user_func_array($fnc, $args);
      }
    }
  }
  
  public function load() {
    //if ($this->dbversion == 'full') return $this->LoadFromDB();
    return tfilestorage::load($this);
  }
  
  public function save() {
    if ($this->lockcount) return;
    if ($this->dbversion) {
      $this->SaveToDB();
    } else {
      tfilestorage::save($this);
    }
  }
  
  public function savetostring() {
    return serialize($this->data);
  }
  
  public function loadfromstring($s) {
    try {
      if (!empty($s)) $this->data = unserialize($s) + $this->data;
      $this->afterload();
      return true;
    } catch (Exception $e) {
      echo 'Caught exception: '.  $e->getMessage() ;
      return false;
    }
  }
  
  public function afterload() {
    foreach ($this->coinstances as $coinstance) {
      if (method_exists($coinstance, 'afterload')) $coinstance->afterload();
    }
  }
  
  public function lock() {
    $this->lockcount++;
  }
  
  public function unlock() {
    if (--$this->lockcount <= 0) $this->save();
  }
  
  public function getlocked() {
    return $this->lockcount  > 0;
  }
  
  public function Getclass() {
    return get_class($this);
  }
  
  public function getdbversion() {
    return false; // dbversion == 'full';
  }
  
  public function getdb($table = '') {
    $table =$table != '' ? $table : $this->table;
    if ($table != '') litepublisher::$db->table = $table;
    return litepublisher::$db;
  }
  
  protected function SaveToDB() {
    $this->db->add($this->getbasename(), $this->savetostring());
  }
  
  protected function LoadFromDB() {
    if ($r = $this->db->select('basename = '. $this->getbasename() . "'")) {
      return $this->loadfromstring($r['data']);
    }
  }
  
  protected function getthistable() {
    if (!litepublisher::$db) $this->error('db');
    return litepublisher::$db->prefix . $this->table;
  }
  
  public static function get_class_name($c) {
    return is_object($c) ? get_class($c) : trim($c);
  }
  
  public static function encrypt($s, $key) {
    $maxkey = mcrypt_get_key_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
    if (strlen($key) > $maxkey) $key = substr($key, $maxkey);
    $block = mcrypt_get_block_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
    $pad = $block - (strlen($s) % $block);
    $s .= str_repeat(chr($pad), $pad);
    return mcrypt_encrypt(MCRYPT_Blowfish, $key, $s, MCRYPT_MODE_ECB);
  }
  
  public static function decrypt($s, $key) {
    $maxkey = mcrypt_get_key_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
    if (strlen($key) > $maxkey) $key = substr($key, $maxkey);
    
    $s = mcrypt_decrypt(MCRYPT_Blowfish, $key, $s, MCRYPT_MODE_ECB);
    $block = mcrypt_get_block_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
    $pad = ord($s[($len = strlen($s)) - 1]);
    return substr($s, 0, strlen($s) - $pad);
  }
  
}//class

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

class tstorage extends tfilestorage {
  public static $data;
  private static $modified;
  
  public static function save(tdata $obj) {
    self::$modified = true;
    $base = $obj->getbasename();
    if (!isset(self::$data[$base])) self::$data[$base] = &$obj->data;
    return true;
  }
  
  public static function load(tdata $obj) {
    $base = $obj->getbasename();
    if (isset(self::$data[$base])) {
      $obj->data = &self::$data[$base];
      $obj->afterload();
      return true;
    } else {
      self::$data[$base] = &$obj->data;
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
      $lock = litepublisher::$paths->data .'storage.lok';
      if (($fh = @fopen($lock, 'w')) &&       flock($fh, LOCK_EX | LOCK_NB)) {
        self::savetofile(litepublisher::$paths->data .'storage', serialize(self::$data));
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
  
}//class

class tarray2prop {
  public $array;
public function __construct(array $a = null) { $this->array = $a; }
public function __destruct() { unset($this->array); }
public function __get($name) { return $this->array[$name]; }
public function __set($name, $value) { $this->array[$name] = $value; }
public function __isset($name) { return array_key_exists($name, $this->array); }
public function __tostring() { return $this->array['']; }
}//class

function sqldate($date = 0) {
  if ($date == 0) $date = time();
  return date('Y-m-d H:i:s', $date);
}

function sqltime($date = 0) {
  if ($date == 0) return '0000-00-00 00:00:00';
  return date('Y-m-d H:i:s', $date);
}

function dbquote($s) {
  return litepublisher::$db->quote($s);
}

function md5rand() {
  return md5(mt_rand() . litepublisher::$secret. microtime());
}

function md5uniq() {
  return basemd5(mt_rand() . litepublisher::$secret. microtime());
}

function basemd5($s) {
  return trim(base64_encode(md5($s, true)), '=');
}

function strbegin($s, $begin) {
  return strncmp($s, $begin, strlen($begin)) == 0;
}

function strbegins() {
  $a = func_get_args();
  $s = array_shift($a);
  while ($begin = array_shift($a)) {
    if (strncmp($s, $begin, strlen($begin)) == 0) return true;
  }
  return false;
}

function strend($s, $end) {
  return $end == substr($s, 0 - strlen($end));
}

function strip_utf($s) {
  $utf = "\xEF\xBB\xBF";
  return strbegin($s, $utf) ? substr($s, strlen($utf)) : $s;
}

function array_delete(array &$a, $i) {
  array_splice($a, $i, 1);
}

function array_delete_value(array &$a, $value) {
  $i = array_search($value, $a);
  if ($i !== false)         array_splice($a, $i, 1);
}

function array_clean(array &$items) {
  $items = array_unique($items);
  foreach (array(0, false, null, '') as $v) {
    $i = array_search($v, $items);
    if ($i !== false)         array_splice($items, $i, 1);
  }
}

function array_insert(array &$a, $item, $index) {
  array_splice($a, $index, 0, array($item));
}

function array_move(array &$a, $oldindex, $newindex) {
  //delete and insert
  if (($oldindex == $newindex) || !isset($a[$oldindex])) return false;
  $item = $a[$oldindex];
  array_splice($a, $oldindex, 1);
  array_splice($a, $newindex, 0, array($item));
}

function strtoarray($s) {
  $a = explode("\n", trim($s));
  foreach ($a as $k => $v) $a[$k] = trim($v);
  return $a;
}

function tojson($a) {
  if (defined('JSON_NUMERIC_CHECK')) {
    return json_encode($a, JSON_NUMERIC_CHECK | (defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0));
  }
  
  return json_encode($a);
}

function jsonattr($a) {
  return str_replace('"', '&quot;', tojson($a));
}

function toenum($v, array $a) {
  $v = trim($v);
  return in_array($v, $a) ? $v : $a[0];
}

function dumpstr($s) {
  echo "<pre>\n", htmlspecialchars($s), "</pre>\n";
}

function dumpvar($v) {
  echo "<pre>\n";
  var_dump($v);
  echo "</pre>\n";
}