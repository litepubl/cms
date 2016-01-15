<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

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
    $this->cache = true;
    $this->data = array();
    $this->coinstances = array();
    $this->coclasses = array();
    $this->basename = substr(get_class($this) , 1);
    $this->create();
  }

  protected function create() {
  }

  public function __get($name) {
    if (method_exists($this, $get = 'get' . $name)) {
      return $this->$get();
    } elseif (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    } else {
      foreach ($this->coinstances as $coinstance) {
        if (isset($coinstance->$name)) {
          return $coinstance->$name;
        }
      }

      return $this->error(sprintf('The requested property "%s" not found in class  %s', $name, get_class($this)));
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

  public function __call($name, $params) {
    if (method_exists($this, strtolower($name))) {
      return call_user_func_array(array(
        $this,
        strtolower($name)
      ) , $params);
    }

    foreach ($this->coinstances as $coinstance) {
      if (method_exists($coinstance, $name) || $coinstance->method_exists($name)) return call_user_func_array(array(
        $coinstance,
        $name
      ) , $params);
    }
    $this->error("The requested method $name not found in class " . get_class($this));
  }

  public function __isset($name) {
    if (array_key_exists($name, $this->data) || method_exists($this, "get$name") || method_exists($this, "Get$name")) {
      return true;
    }

    foreach ($this->coinstances as $coinstance) {
      if (isset($coinstance->$name)) {
        return true;
      }
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
        $file = $dir . $externalname;
        if (!file_exists($file)) return;
      }

      include_once ($file);

      $fnc = $class . $func;
      if (function_exists($fnc)) {
        //$fnc($this, $arg);
        if (is_array($args)) {
          array_unshift($args, $this);
        } else {
          $args = array(
            $this,
            $args
          );
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
      if (!empty($s)) {
        $this->data = unserialize($s) + $this->data;
      }

      $this->afterload();
      return true;
    }
    catch(Exception $e) {
      echo 'Caught exception: ' . $e->getMessage();
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
    return $this->lockcount > 0;
  }

  public function Getclass() {
    return get_class($this);
  }

  public function getdbversion() {
    return false; // dbversion == 'full';
    
  }

  public function getdb($table = '') {
    $table = $table ? $table : $this->table;
    if ($table) litepublisher::$db->table = $table;
    return litepublisher::$db;
  }

  protected function SaveToDB() {
    $this->db->add($this->getbasename() , $this->savetostring());
  }

  protected function LoadFromDB() {
    if ($r = $this->db->select('basename = ' . $this->getbasename() . "'")) {
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
    $s.= str_repeat(chr($pad) , $pad);
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

} //class