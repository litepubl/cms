<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

class Session
 {
    public static $initialized = false;
    public $prefix;
    public $lifetime;

    public function __construct() {
        $this->prefix = 'ses-' .  $this->getApp()->domain . '-';
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
        return  $this->getApp()->memcache->get($this->prefix . $id);
    }

    public function write($id, $data) {
        return  $this->getApp()->memcache->set($this->prefix . $id, $data, false, $this->lifetime);
    }

    public function destroy($id) {
        return  $this->getApp()->memcache->delete($this->prefix . $id);
    }

    public static function init($usecookie = false) {
        if (!static ::$initialized) {
            static ::$initialized = true;
            ini_set('session.use_cookies', $usecookie);
            ini_set('session.use_only_cookies', $usecookie);
            ini_set('session.use_trans_sid', 0);
            session_cache_limiter(false);

            if (function_exists('igbinary_serialize')) {
                ini_set('igbinary.compact_strings', 0);
                ini_set('session.serialize_handler', 'igbinary');
            }
        }

        if ( $this->getApp()->memcache) {
            return static::iGet(__class__);
        } else {
            //ini_set('session.gc_probability', 1);
            
        }
    }

    public static function start($id) {
        $r = static ::init(false);
        session_id($id);
        session_start();
        return $r;
    }

}