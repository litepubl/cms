<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tsession {
  public static $initialized = false;
  public $prefix;
  public $lifetime;

  public function __construct() {
    $this->prefix = 'ses-' . litepubl::$domain . '-';
    $this->lifetime = 3600;
    $truefunc = array(
      $this,
      'truefunc'
    );
    session_set_save_handler($truefunc, $truefunc, array(
      $this,
      'read'
    ) , array(
      $this,
      'write'
    ) , array(
      $this,
      'destroy'
    ) , $truefunc);
  }

  public function truefunc() {
    return true;
  }

  public function read($id) {
    return litepubl::$memcache->get($this->prefix . $id);
  }

  public function write($id, $data) {
    return litepubl::$memcache->set($this->prefix . $id, $data, false, $this->lifetime);
  }

  public function destroy($id) {
    return litepubl::$memcache->delete($this->prefix . $id);
  }

  public static function init($usecookie = false) {
    if (!static::$initialized) {
      static::$initialized = true;
      ini_set('session.use_cookies', $usecookie);
      ini_set('session.use_only_cookies', $usecookie);
      ini_set('session.use_trans_sid', 0);
      session_cache_limiter(false);

      if (function_exists('igbinary_serialize')) {
        ini_set('igbinary.compact_strings', 0);
        ini_set('session.serialize_handler', 'igbinary');
      }
    }

    if (litepubl::$memcache) {
      return getinstance(__class__);
    } else {
      //ini_set('session.gc_probability', 1);
      
    }
  }

  public static function start($id) {
    $r = static::init(false);
    session_id($id);
    session_start();
    return $r;
  }

} //class