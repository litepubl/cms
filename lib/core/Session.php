<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

use litepubl\Config;

class Session
 {
    public static $initialized = false;
    public static $instance = false;

public $memcache;
    public $prefix;
    public $lifetime;

    public function __construct($memcache, $prefix) {
$this->memcache = $memcache;
        $this->prefix = 'ses-' . $prefix;
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
        return  $this->memcache->get($this->prefix . $id);
    }

    public function write($id, $data) {
        return  $this->memcache->set($this->prefix . $id, $data, false, $this->lifetime);
    }

    public function destroy($id) {
        return  $this->memcache->delete($this->prefix . $id);
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

$app = litepubl::$app;
        if ($app->memcache) {
            static::$instance = new static($app->memcache, $app->controller ? $app->controller->host : Config::$host);
        } else {
            //ini_set('session.gc_probability', 1);
                 }
}

return static::$instance;
    }

    public static function start($id) {
        $r = static ::init(false);
        session_id($id);
        session_start();
        return $r;
    }

}