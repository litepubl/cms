<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsession {
  public static $initialized = false;
  public $prefix;
  public $lifetime;
  
  public function __construct () {
    $this->prefix = 'ses-' . litepublisher::$domain . '-';
    $this->lifetime = 3600;
    $truefunc = array($this, 'truefunc');
    session_set_save_handler($truefunc,$truefunc, array($this,'read'), array($this,'write'), array($this,'destroy'), $truefunc);
  }
  
  public function truefunc() {
    return true;
  }
  
  public function read($id) {
    return tfilestorage::$memcache->get($this->prefix . $id);
  }
  
  public function write($id,$data) {
    return tfilestorage::$memcache->set($this->prefix . $id,$data, false, $this->lifetime);
  }
  
  public function destroy($id) {
    return tfilestorage::$memcache->delete($this->prefix . $id);
  }
  
  public static function init($usecookie = false) {
    if (!self::$initialized) {
      self::$initialized = true;
      ini_set('session.use_cookies', $usecookie);
      ini_set('session.use_only_cookies', $usecookie);
      ini_set('session.use_trans_sid', 0);
      session_cache_limiter(false);

if (function_exists('igbinary_serialize')) {
ini_set('igbinary.compact_strings', 0);
ini_set('session.serialize_handler', 'igbinary');
}
    }
    
    if (tfilestorage::$memcache) {
      return getinstance(__class__);
    } else {
      //ini_set('session.gc_probability', 1);
    }
  }
  
  public static function start($id) {
    $r = self::init(false);
    session_id ($id);
    session_start();
    return $r;
  }
  
}//class