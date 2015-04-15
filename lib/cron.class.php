<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcron extends tevents {
  public static $pinged = false;
  public $disableadd;
  private $socket;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'cron';
    $this->addevents('added', 'deleted');
    $this->data['password'] = '';
    $this->data['path'] = '';
    $this->data['disableping'] = false;
    $this->cache = false;
    $this->disableadd = false;
    $this->table = 'cron';
  }
  
  protected function geturl() {
    return sprintf('/croncron.htm%scronpass=%s', litepublisher::$site->q, urlencode($this->password));
  }
  
  public function getlockpath() {
    if ($result = $this->path) {
      if (is_dir($result)) return $result;
    }
    return litepublisher::$paths->data;
  }
  
  public function request($arg) {
    if (!isset($_GET['cronpass']) || ($this->password != $_GET['cronpass'])) return 403;
    if (($fh = @fopen($this->lockpath .'cron.lok', 'w')) &&       flock($fh, LOCK_EX | LOCK_NB)) {
      try {
        set_time_limit(300);
        if (litepublisher::$debug) {
          ignore_user_abort(true);
        } else {
          litepublisher::$urlmap->close_connection();
        }
        
        if (ob_get_level()) ob_end_flush ();
        flush();
        
        $this->sendexceptions();
        $this->log("started loop");
        $this->execute();
      } catch (Exception $e) {
        litepublisher::$options->handexception($e);
      }
      flock($fh, LOCK_UN);
      fclose($fh);
      @chmod($this->lockpath .'cron.lok', 0666);
      $this->log("finished loop");
      return 'Ok';
    }
    return 'locked';
  }
  
  public function run() {
    if (ob_get_level()) ob_end_flush ();
    flush();
    
    if (($fh = @fopen($this->lockpath .'cron.lok', 'w')) &&       flock($fh, LOCK_EX | LOCK_NB)) {
      set_time_limit(300);
      //ignore_user_abort(true);
      
      try {
        $this->execute();
      } catch (Exception $e) {
        litepublisher::$options->handexception($e);
      }
      
      flock($fh, LOCK_UN);
      fclose($fh);
      @chmod($this->lockpath .'cron.lok', 0666);
      return true;
    }
    
    return false;
  }
  
  public function execute() {
    while ($item = $this->db->getassoc(sprintf("date <= '%s' order by date asc limit 1", sqldate()))) {
      extract($item);
  $this->log("task started:\n{$class}->{$func}($arg)");
      $arg = unserialize($arg);
      if ($class == '' ) {
        if (function_exists($func)) {
          try {
            $func($arg);
          } catch (Exception $e) {
            litepublisher::$options->handexception($e);
          }
        } else {
          $this->db->iddelete($id);
          continue;
        }
      } elseif (class_exists($class)) {
        try {
          $obj = getinstance($class);
          $obj->$func($arg);
        } catch (Exception $e) {
          litepublisher::$options->handexception($e);
        }
      } else {
        $this->db->iddelete($id);
        continue;
      }
      if ($type == 'single') {
        $this->db->iddelete($id);
      } else {
        $this->db->setvalue($id, 'date', sqldate(strtotime("+1 $type")));
      }
    }
  }
  
  public function add($type, $class, $func, $arg = null) {
    if (!preg_match('/^single|hour|day|week$/', $type)) $this->error("Unknown cron type $type");
    if ($this->disableadd) return false;
    $id = $this->doadd($type, $class, $func, $arg);
    
    if (($type == 'single') && !$this->disableping && !self::$pinged) {
      if (litepublisher::$debug) tfiler::log("cron added $id");
      if (tfilestorage::$memcache) {
        $this->pingmemcache();
      } else {
        self::pingonshutdown();
      }
    }
    
    return $id;
  }
  
  protected function doadd($type, $class, $func, $arg ) {
    $id = $this->db->add(array(
    'date' => sqldate(),
    'type' => $type,
    'class' =>  $class,
    'func' => $func,
    'arg' => serialize($arg)
    ));
    
    $this->added($id);
    return $id;
  }
  
  public function addnightly($class, $func, $arg) {
    $id = $this->db->add(array(
    'date' => date('Y-m-d 03:15:00', time()),
    'type' => 'day',
    'class' =>  $class,
    'func' => $func,
    'arg' => serialize($arg)
    ));
    $this->added($id);
    return $id;
  }
  
  public function addweekly($class, $func, $arg) {
    $id = $this->db->add(array(
    'date' => date('Y-m-d 03:15:00', time()),
    'type' => 'week',
    'class' =>  $class,
    'func' => $func,
    'arg' => serialize($arg)
    ));
    
    $this->added($id);
    return $id;
  }
  
  public function delete($id) {
    $this->db->iddelete($id);
    $this->deleted($id);
  }
  
  public function deleteclass($c) {
    $class = self::get_class_name($c);
    $this->db->delete("class = '$class'");
  }
  
  public function pingmemcache() {
    $memcache = tfilestorage::$memcache;
    $expired = time() - 300;
    $key_last =litepublisher::$domain . ':lastpinged';
    $lastpinged = $memcache->get($key_last );
    if ($lastpinged && ($expired >= $lastpinged)) {
      return self::pingonshutdown();
    }
    
    $key_single =litepublisher::$domain . ':singlepinged';
    $singlepinged = $memcache->get($key_single);
    if (!$singlepinged) {
      $memcache->set($key_single, time(), false, 3600);
    } elseif ($expired >= $singlepinged ) {
      self::pingonshutdown();
    }
    
  }
  
  public static function pingonshutdown() {
    if (self::$pinged) return;
    self::$pinged = true;
    
    if (tfilestorage::$memcache) {
      $memcache = tfilestorage::$memcache;
      $k =litepublisher::$domain . ':lastpinged';
      $memcache->set($k, time(), false, 3600);
      $k =litepublisher::$domain . ':singlepinged';
      $memcache->delete($k);
    }
    
    register_shutdown_function(array(tcron::i(), 'ping'));
  }
  
  public function ping() {
    $p = parse_url(litepublisher::$site->url . $this->url);
    $this->pinghost($p['host'], $p['path'] . (empty($p['query']) ? '' : '?' . $p['query']));
  }
  
  private function pinghost($host, $path) {
    //$this->log("pinged host $host$path");
    if (		$this->socket = @fsockopen( $host, 80, $errno, $errstr, 0.10)) {
      fputs($this->socket, "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n");
      //0.01 sec
      usleep(10000);
    }
  }
  
  public function sendexceptions() {
    $filename = litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR . 'exceptionsmail.log';
    if (!file_exists($filename)) return;
    $time = @filectime ($filename);
    if (($time === false) || ($time + 3600 > time())) return;
    $s = file_get_contents($filename);
    tfilestorage::delete($filename);
    tmailer::SendAttachmentToAdmin('[error] '. litepublisher::$site->name, 'See attachment', 'errors.txt', $s);
    sleep(2);
  }
  
  public function log($s) {
    echo date('r') . "\n$s\n\n";
    flush();
    if (litepublisher::$debug) tfiler::log($s, 'cron.log');
  }
  
}//class