<?php
//$_COOKIE = array ( 'litepubl_regservice' => 'twitter', 'litepubl_user_id' => '1', 'litepubl_user' => '3U+bl6S+No/lHRd3mGTP7g', 'litepubl_user_flag' => 'true', );
//$_COOKIE = array ( 'litepubl_regservice' => 'twitter', 'litepubl_user_id' => '3', 'litepubl_user' => 'Nc241SNn1C/VIOkJ0pNeNQ', );
//set_time_limit(4);
error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
ini_set('display_errors', 1);
 Header( 'Cache-Control: no-cache, must-revalidate');
  Header( 'Pragma: no-cache');

class litepublisher {
  public static $db;
  public static $storage;
  public static $classes;
  public static $options;
  public static $site;
  public static $urlmap;
  public static $paths;
  public static $domain;
  public static $debug = true;
  public static $secret = '8r7j7hbt8iik//pt7hUy5/e/7FQvVBoh7/Zt8sCg8+ibVBUt7rQ';
  public static $microtime;
  
  public static function init() {
    if (defined('litepublisher_mode') && (litepublisher_mode == 'debug')) litepublisher::$debug = true;
    if (!preg_match('/(www\.)?([\w\.\-]+)(:\d*)?/', strtolower(trim($_SERVER['HTTP_HOST'])) , $domain)) die('cant resolve domain name');
    self::$domain = $domain[2];

    $home = dirname(__file__) . DIRECTORY_SEPARATOR;
    $storage = $home . 'storage' . DIRECTORY_SEPARATOR;

$paths = new tpaths();
    self::$paths = $paths;
    $paths->home = $home;
    $paths->lib = $home .'lib'. DIRECTORY_SEPARATOR;
    $paths->data = $storage . 'data'. DIRECTORY_SEPARATOR;
    $paths->cache = $storage . 'cache'. DIRECTORY_SEPARATOR;
    $paths->libinclude = $home .'lib'. DIRECTORY_SEPARATOR . 'include'. DIRECTORY_SEPARATOR;
    $paths->languages = $home .'lib'. DIRECTORY_SEPARATOR . 'languages'. DIRECTORY_SEPARATOR;
    $paths->storage = $storage;
    $paths->backup = $storage . 'backup' . DIRECTORY_SEPARATOR;
    $paths->plugins =  $home . 'plugins' . DIRECTORY_SEPARATOR;
    $paths->themes = $home . 'themes'. DIRECTORY_SEPARATOR;
    $paths->files = $home . 'files' . DIRECTORY_SEPARATOR;
   $paths->js = $home . 'js' . DIRECTORY_SEPARATOR;
    self::$microtime = microtime(true);
  }
  
}//class

class tpaths {
public $home;
public $lib;
public $data;
public $cache;
public $backup;
public $storage;
public $libinclude;
public $js;
public $plugins;
public $themes;
public $files;
}

try {
  litepublisher::init();
if (litepublisher::$domain== 'fireflyblog.ru') {
define('dbversion' , false);
litepublisher::$paths->data .= 'fire\\';
}

if (litepublisher::$debug) {
//require_once(litepublisher::$paths->lib . 'debugproxy.class.php');
require_once(litepublisher::$paths->lib . 'data.class.php');
    require_once(litepublisher::$paths->lib . 'storage.file.class.php');
    require_once(litepublisher::$paths->lib . 'storage.class.php');
require_once(litepublisher::$paths->lib . 'events.class.php');
require_once(litepublisher::$paths->lib . 'items.class.php');
require_once(litepublisher::$paths->lib . 'classes.class.php');
require_once(litepublisher::$paths->lib . 'options.class.php');
require_once(litepublisher::$paths->lib . 'site.class.php');
} else {
require_once(litepublisher::$paths->lib . 'kernel.php');
}

/*
if (class_exists('Memcache')) {
tfilestorage::$memcache =  new Memcache;
tfilestorage::$memcache->connect('127.0.0.1', 11211);
}
*/

if (!tstorage::loaddata()) {
if (file_exists(litepublisher::$paths->data . 'storage.php') && filesize(litepublisher::$paths->data . 'storage.php')) die('Storage not loaded');
  //if (!litepublisher::$options->installed) require_once(litepublisher::$paths->lib .'install' . DIRECTORY_SEPARATOR . 'install.php');
require_once(litepublisher::$paths->lib .'install' . DIRECTORY_SEPARATOR . 'install.php');
}

  litepublisher::$classes = tclasses::i();
  litepublisher::$options = toptions::i();
litepublisher::$db = new tdatabase();
  litepublisher::$site = tsite::i();
  litepublisher::$urlmap = turlmap::i();

/*
litepublisher::$db->query('SET sort_buffer_size = ' . 1024*1024*32);
litepublisher::$db->query('SET read_rnd_buffer_size = ' . 1024*1024*32);
*/

 tlocal::clearcache();
//ttheme::clearcache();
include(dirname(__file__) . '/temp/zdebug.php');
  if (!defined('litepublisher_mode')) {
    litepublisher::$urlmap->request(strtolower($_SERVER['HTTP_HOST']), $_SERVER['REQUEST_URI']);
  }
} catch (Exception $e) {
// echo $e->GetMessage();
litepublisher::$options->handexception($e);
}
litepublisher::$options->savemodified();
litepublisher::$options->showerrors();

/*
echo "<pre>\n";
$man = tdbmanager::i();
echo $man->performance();
echo round(microtime(true) - litepublisher::$microtime, 2), "\n";
*/

//tdebugproxy::showperformance();

