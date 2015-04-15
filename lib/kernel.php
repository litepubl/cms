<?php
//db.class.php
class tdatabase {
  public $mysqli;
  public $result;
  public $sql;
  public $dbname;
  public $table;
  public $prefix;
  public $history;
  public $debug;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function instance() {
    return self::i();
  }
  
  public function __construct() {
    $this->table = '';
    $this->sql = '';
    $this->history = array();
    
    $this->setconfig($this->getconfig());
  }
  
  public function getconfig() {
    $this->debug = &litepublisher::$debug;
    if (isset(litepublisher::$options->dbconfig)) {
      $result = litepublisher::$options->dbconfig;
      //decrypt db password
      $result['password'] = litepublisher::$options->dbpassword;
      return $result;
    }
    
    return false;
  }
  
  public function setconfig($dbconfig) {
    if (!$dbconfig) return false;
    $this->dbname =  $dbconfig['dbname'];
    $this->prefix =  $dbconfig['prefix'];
    
    $this->mysqli = new mysqli($dbconfig['host'], $dbconfig['login'], $dbconfig['password'], $dbconfig['dbname'], $dbconfig['port'] > 0 ?  $dbconfig['port'] : null);
    
    if (mysqli_connect_error()) {
      throw new Exception('Error connect to database');
    }
    
    $this->mysqli->set_charset('utf8');
    //$this->query('SET NAMES utf8');
    
    /* lost performance
    $timezone = date('Z') / 3600;
    if ($timezone > 0) $timezone = "+$timezone";
    $this->query("SET time_zone = '$timezone:00'");
    */
  }
  
  /*
  public function __destruct() {
    if (is_object($this)) {
      if (is_object($this->mysqli)) $this->mysqli->close();
      $this->mysqli = false;
    }
  }
  */
  
  public function __get ($name) {
    return $this->prefix . $name;
  }
  
  public function  exec($sql) {
    return $this->query($sql);
  }
  
  public function query($sql) {
    $this->sql = $sql;
    if ($this->debug) {
      $this->history[] = array(
      'sql' => $sql,
      'time' => 0
      );
      $microtime = microtime(true);
    }
    
    if (is_object($this->result)) $this->result->close();
    $this->result = $this->mysqli->query($sql);
    if ($this->debug) {
      $this->history[count($this->history) - 1]['time'] = microtime(true) - $microtime;
      if ($this->mysqli->warning_count && ($r = $this->mysqli->query('SHOW WARNINGS'))) {
        echo "<pre>\n";
        echo $sql, "\n";
        var_dump($r->fetch_assoc ());
        echo "</pre>\n";
      }
    }
    if ($this->result == false) {
      $this->doerror($this->mysqli->error);
    }
    return $this->result;
  }
  
  protected function doerror($mesg) {
    if (!$this->debug) return litepublisher::$options->trace($this->sql . "\n" . $mesg)
    ;
    $log = "exception:\n$mesg\n$this->sql\n";
    try {
      throw new Exception();
    } catch (Exception $e) {
      $log .=str_replace(litepublisher::$paths->home, '', $e->getTraceAsString());
    }
    
    $log .= $this->performance();
    $log = str_replace("\n", "<br />\n", htmlspecialchars($log));
    die($log);
  }
  
  public function performance() {
    $result = '';
    $total = 0.0;
    $max = 0.0;
    foreach ($this->history as $i => $item) {
  $result .= "$i: {$item['time']}\n{$item['sql']}\n\n";
      $total += $item['time'];
      if ($max < $item['time']) {
        $maxsql = $item['sql'];
        $max = $item['time'];
      }
    }
    $result .= "maximum $max\n$maxsql\n";
    $result .= sprintf("%s total time\n%d querries\n\n", $total, count($this->history));
    return $result;
  }
  
  public function quote($s) {
    return sprintf('\'%s\'', $this->mysqli->real_escape_string($s));
  }
  
  public function escape($s) {
    return $this->mysqli->real_escape_string($s);
  }
  
  public function settable($table) {
    $this->table = $table;
    return $this;
  }
  
  public function select($where) {
    if ($where != '') $where = 'where '. $where;
    return $this->query("SELECT * FROM $this->prefix$this->table $where");
  }
  
  public function idselect($where) {
    return $this->res2id($this->query("select id from $this->prefix$this->table where $where"));
  }
  
  public function selectassoc($sql) {
    return $this->query($sql)->fetch_assoc();
  }
  
  public function getassoc($where) {
    return $this->select($where)->fetch_assoc();
  }
  
  public function update($values, $where) {
    return $this->query("update $this->prefix$this->table set $values   where $where");
  }
  
  public function idupdate($id, $values) {
    return $this->update($values, "id = $id");
  }
  
  public function assoc2update(array $a) {
    $list = array();
    foreach ($a As $name => $value) {
      if (is_bool($value)) {
        $value =$value ? '1' : '0';
        $list[] = sprintf('%s = %s ', $name, $value);
        continue;
      }
      
      $list[] = sprintf('%s = %s', $name,  $this->quote($value));
    }
    
    return implode(', ', $list);
  }
  
  public function updateassoc(array $a, $index = 'id') {
    $id = $a[$index];
    unset($a[$index]);
    return $this->update($this->assoc2update($a), "$index = '$id' limit 1");
  }
  
  public function insertrow($row) {
    return $this->query(sprintf('INSERT INTO %s%s %s', $this->prefix, $this->table, $row));
  }
  
  public function insertassoc(array $a) {
    unset($a['id']);
    return $this->add($a);
  }
  
  public function addupdate(array $a) {
    if ($this->idexists($a['id'])) {
      $this->updateassoc($a);
    } else {
      return $this->add($a);
    }
  }
  
  public function add(array $a) {
    $this->insertrow($this->assoctorow($a));
    if ($id = $this->mysqli->insert_id) return $id;
    $r = $this->query('select last_insert_id() from ' . $this->prefix . $this->table)->fetch_row();
    return (int) $r[0];
  }
  
  public function insert(array $a) {
    $this->insertrow($this->assoctorow($a));
  }
  
  public function assoctorow(array $a) {
    $vals = array();
    foreach( $a as $name => $val) {
      if (is_bool($val)) {
        $vals[] = $val ? '1' : '0';
      } else {
        $vals[] = $this->quote($val);
      }
    }
    return sprintf('(%s) values (%s)', implode(', ', array_keys($a)), implode(', ', $vals));
  }
  
  public function getcount($where = '') {
    $sql = "SELECT COUNT(*) as count FROM $this->prefix$this->table";
    if ($where) $sql .= ' where '. $where;
    if (($res = $this->query($sql)) && ($r = $res->fetch_assoc())) {
      return (int) $r['count'];
    }
    return false;
  }
  
  public function delete($where) {
    return $this->query("delete from $this->prefix$this->table where $where");
  }
  
  public function iddelete($id) {
    return $this->query("delete from $this->prefix$this->table where id = $id");
  }
  
  public function deleteitems(array $items) {
    return $this->delete('id in ('. implode(', ', $items) . ')');
  }
  
  public function idexists($id) {
    if ($r = $this->query("select id  from $this->prefix$this->table where id = $id limit 1")->fetch_assoc()) return true;
    return false;
  }
  
  public function  exists($where) {
    return $this->query("select *  from $this->prefix$this->table where $where limit 1")->num_rows;
  }
  
  public function getlist(array $list) {
    return $this->res2assoc($this->select(sprintf('id in (%s)', implode(',', $list))));
  }
  
  public function getitems($where) {
    return $this->res2assoc($this->select($where));
  }
  
  public function getitem($id, $propname = 'id') {
    if ($r = $this->query("select * from $this->prefix$this->table where $propname = $id limit 1")) return $r->fetch_assoc();
    return false;
  }
  
  public function finditem($where) {
    return $this->query("select * from $this->prefix$this->table where $where limit 1")->fetch_assoc();
  }
  
  public function findid($where) {
    return $this->findprop('id', $where);
  }
  
  public function findprop($propname, $where) {
    if($r = $this->query("select $propname from $this->prefix$this->table where $where limit 1")->fetch_assoc()) return $r[$propname];
    return false;
  }
  
  public function getval($table, $id, $name) {
    if ($r = $this->query("select $name from $this->prefix$table where id = $id limit 1")->fetch_assoc()) return $r[$name];
    return false;
  }
  
  public function getvalue($id, $name) {
    if ($r = $this->query("select $name from $this->prefix$this->table where id = $id limit 1")->fetch_assoc()) return $r[$name];
    return false;
  }
  
  public function setvalue($id, $name, $value) {
    return $this->update("$name = " . $this->quote($value), "id = $id");
  }
  
  public function getvalues($name, $where) {
    $result = array();
    $res = $this->query("select $name from $this->prefix$this->table where $where");
    if (is_object($res)) {
      while ($r = $res->fetch_row()) {
        $result[$r[0]] = $r[1];
      }
    }
    return $result;
  }
  
  public function res2array($res) {
    $result = array();
    if (is_object($res)) {
      while ($row = $res->fetch_row()) {
        $result[] = $row;
      }
      return $result;
    }
  }
  
  public function res2id($res) {
    $result = array();
    if (is_object($res)) {
      while ($row = $res->fetch_row()) {
        $result[] = $row[0];
      }
    }
    return $result;
  }
  
  public function res2assoc($res) {
    $result = array();
    if (is_object($res)) {
      while ($r = $res->fetch_assoc()) {
        $result[] = $r;
      }
    }
    return $result;
  }
  
  public function res2items($res) {
    $result = array();
    if (is_object($res)) {
      while ($r = $res->fetch_assoc()) {
        $result[(int) $r['id']] = $r;
      }
    }
    return $result;
  }
  
  public function fetchassoc($res) {
    return is_object($res) ? $res->fetch_assoc() : false;
  }
  
  public function fetchnum($res) {
    return is_object($res) ? $res->fetch_row() : false;
  }
  
  public function countof($res) {
    return  is_object($res) ? $res->num_rows : 0;
  }
  
  public static function str2array($s) {
    $result = array();
    foreach (explode(',', $s) as $i => $value) {
      $v = (int) trim($value);
      if ($v== 0) continue;
      $result[] = $v;
    }
    return $result;
  }
  
}//class

//data.class.php
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

//events.class.php
class ECancelEvent extends Exception {
  public $result;
  
  public function __construct($message, $code = 0) {
    $this->result = $message;
    parent::__construct('', 0);
  }
}

class tevents extends tdata {
  protected $events;
  protected $eventnames;
  protected $map;
  
  public function __construct() {
    $this->eventnames = array();
    $this->map = array();
    parent::__construct();
    $this->assignmap();
    $this->load();
  }
  
  public function __destruct() {
    unset($this->data, $this->events, $this->eventnames, $this->map);
  }
  
  protected function create() {
    $this->addmap('events', array());
    $this->addmap('coclasses', array());
  }
  
  public function assignmap() {
    foreach ($this->map as $propname => $key) {
      $this->$propname = &$this->data[$key];
    }
  }
  
  public function afterload() {
    $this->assignmap();
    foreach ($this->coclasses as $coclass) {
      $this->coinstances[] = getinstance($coclass);
    }
    parent::afterload();
  }
  
  protected function addmap($name, $value) {
    $this->map[$name] = $name;
    $this->data[$name] = $value;
    $this->$name = &$this->data[$name];
  }
  
  public function free() {
    unset(litepublisher::$classes->instances[get_class($this)]);
    foreach ($this->coinstances as $coinstance) $coinstance->free();
  }
  
  public function eventexists($name) {
    return in_array($name, $this->eventnames);
  }
  
  public function __get($name) {
    if (method_exists($this, $name)) return array('class' =>get_class($this), 'func' => $name);
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if (parent::__set($name, $value)) return true;
    if (in_array($name, $this->eventnames)) {
      $this->addevent($name, $value['class'], $value['func']);
      return true;
    }
    $this->error(sprintf('Unknown property %s in class %s', $name, get_class($this)));
  }
  
  public function method_exists($name) {
    return in_array($name, $this->eventnames);
  }
  
  public  function __call($name, $params) {
    if (in_array($name, $this->eventnames)) return $this->callevent($name, $params);
    parent::__call($name, $params);
  }
  
  public function __isset($name) {
    if (parent::__isset($name)) return true;
    return in_array($name, $this->eventnames);
  }
  
  protected function addevents() {
    $a = func_get_args();
    array_splice($this->eventnames, count($this->eventnames), 0, $a);
  }
  
  private function get_events($name) {
    return isset($this->events[$name]) ? $this->events[$name] : false;
  }
  
  public function callevent($name, $params) {
    $result = '';
    if (    $list = $this->get_events($name)) {
      
      foreach ($list as $i => $item) {
        if (empty($item['class'])) {
          if (function_exists($item['func'])) {
            $call = $item['func'];
          } else {
            $this->delete_event_item($name, $i);
            continue;
          }
        } elseif (!class_exists($item['class'])) {
          $this->delete_event_item($name, $i);
          continue;
        } else {
          $obj = getinstance($item['class']);
          $call = array($obj, $item['func']);
        }
        try {
          $result = call_user_func_array($call, $params);
        } catch (ECancelEvent $e) {
          return $e->result;
        }
      }
    }
    
    return $result;
  }
  
  public static function cancelevent($result) {
    throw new ECancelEvent($result);
  }
  
  private function delete_event_item($name, $i) {
    array_splice($this->events[$name], $i, 1);
    if (count($this->events[$name]) == 0) unset($this->events[$name]);
    $this->save();
  }
  
  public function setevent($name, $params) {
    return $this->addevent($name, $params['class'], $params['func']);
  }
  
  public function addevent($name, $class, $func) {
    if (!in_array($name, $this->eventnames)) return $this->error(sprintf('No such %s event', $name ));
    if (empty($func)) return false;
    if (isset($this->events[$name])) {
      if ($list = $this->get_events($name)) {
        foreach ($list  as $event) {
          if (($event['class'] == $class) && ($event['func'] == $func)) return false;
        }
      }
    } else {
      $this->events[$name] =array();
    }
    
    $this->events[$name][] = array(
    'class' => $class,
    'func' => $func
    );
    $this->save();
  }
  
  public function delete_event_class($name, $class) {
    if (isset($this->events[$name])) {
      $list = &$this->events[$name];
      $deleted = false;
      for ($i = count($list) - 1; $i >= 0; $i--) {
        if ($list[$i]['class'] == $class) {
          array_splice($list, $i, 1);
          $deleted = true;
        }
      }
      if ($deleted) {
        if (count($list) == 0) unset($this->events[$name]);
        $this->save();
      }
      return $deleted;
    }
    return false;
  }
  
  public function unsubscribeclass($obj) {
    $this->unbind($obj);
  }
  
  public function unsubscribeclassname($class) {
    $this->unbind($class);
  }
  
  public function unbind($c) {
    $class = self::get_class_name($c);
    foreach ($this->events as $name => $events) {
      foreach ($events as $i => $item) {
        if ($item['class'] == $class) array_splice($this->events[$name], $i, 1);
      }
    }
    
    $this->save();
  }
  
  public function seteventorder($eventname, $c, $order) {
    if (!isset($this->events[$eventname])) return false;
    $events = &$this->events[$eventname];
    $class = self::get_class_name($c);
    $count = count($events);
    if (($order < 0) || ($order >= $count)) $order = $count - 1;
    foreach ($events as $i => $event) {
      if ($class == $event['class']) {
        if ($i == $order) return true;
        array_splice($events, $i, 1);
        array_splice($events, $order, 0, array(0 => $event));
        $this->save();
        return true;
      }
    }
  }
  
  private function indexofcoclass($class) {
    return array_search($class, $this->coclasses);
  }
  
  public function addcoclass($class) {
    if ($this->indexofcoclass($class) === false) {
      $this->coclasses[] = $class;
      $this->save();
      $this->coinstances = getinstance($class);
    }
  }
  
  public function deletecoclass($class) {
    $i = $this->indexofcoclass($class);
    if (is_int($i)) {
      array_splice($this->coclasses, $i, 1);
      $this->save();
    }
  }
  
}//class

class tevents_storage extends tevents {
  
  public function load() {
    return tstorage::load($this);
  }
  
  public function save() {
    return tstorage::save($this);
  }
  
}//class

class tcoevents extends tevents {
  private $owner;
  
  public function __construct() {
    parent::__construct();
    $a = func_get_args();
    $owner = array_shift ($a);
    $this->owner = $owner;
    if (!isset($owner->data['events'])) $owner->data['events'] = array();
    $this->events = &$owner->data['events'];
    array_splice($this->eventnames, count($this->eventnames), 0, $a);
  }
  
  public function __destruct() {
    parent::__destruct();
    unset($this->owner);
  }
  
public function assignmap() {}
protected function create() { }
public function load() {}
  public function afterload() {
    $this->events = &$this->owner->data['events'];
  }
  
  public function save() {
    return $this->owner->save();
  }
  
}//class

//items.class.php
class titems extends tevents {
  public $items;
  public $dbversion;
  protected $idprop;
  protected $autoid;
  
  protected function create() {
    parent::create();
    $this->addevents('added', 'deleted');
    $this->idprop = 'id';
    if ($this->dbversion) {
      $this->items = array();
    } else {
      $this->addmap('items', array());
      $this->addmap('autoid', 0);
    }
  }
  
  public function load() {
    if ($this->dbversion) {
      return tstorage::load($this);
    } else {
      return parent::load();
    }
  }
  
  public function save() {
    if ($this->lockcount > 0) return;
    if ($this->dbversion) {
      return tstorage::save($this);
    } else {
      return parent::save();
    }
  }
  
  public function loadall() {
    if (!$this->dbversion)  return;
    return $this->select('', '');
  }
  
  public function loaditems(array $items) {
    if (!$this->dbversion) return;
    //exclude loaded items
    $items = array_diff($items, array_keys($this->items));
    if (count($items) == 0) return;
    $list = implode(',', $items);
    $this->select("$this->thistable.$this->idprop in ($list)", '');
  }
  
  public function select($where, $limit) {
    if (!$this->dbversion) $this->error('Select method must be called ffrom database version');
    if ($where != '') $where = 'where '. $where;
    return $this->res2items($this->db->query("SELECT * FROM $this->thistable $where $limit"));
  }
  
  public function res2items($res) {
    if (!$res) return array();
    $result = array();
    $db = litepublisher::$db;
    while ($item = $db->fetchassoc($res)) {
      $id = $item[$this->idprop];
      $result[] = $id;
      $this->items[$id] = $item;
    }
    return $result;
  }
  
  public function getcount() {
    if ($this->dbversion) {
      return $this->db->getcount();
    } else {
      return count($this->items);
    }
  }
  
  public function getitem($id) {
    if (isset($this->items[$id])) return $this->items[$id];
    if ($this->dbversion) {
      if ($this->select("$this->thistable.$this->idprop = $id", 'limit 1')) return $this->items[$id];
    }
    return $this->error(sprintf('Item %d not found in class %s', $id, get_class($this)));
  }
  
  public function getvalue($id, $name) {
    if ($this->dbversion && !isset($this->items[$id])) $this->items[$id] = $this->db->getitem($id, $this->idprop);
    return $this->items[$id][$name];
  }
  
  public function setvalue($id, $name, $value) {
    $this->items[$id][$name] = $value;
    if ($this->dbversion) {
      //$this->db->setvalue($id, $name, $value);
      $this->db->update("$name = " . dbquote($value), "$this->idprop = $id");
    }
  }
  
  public function itemexists($id) {
    if (isset($this->items[$id])) return true;
    if ($this->dbversion) {
      try {
        return $this->getitem($id);
      } catch (Exception $e) {
        return false;
      }
    }
    return false;
  }
  
  public function indexof($name, $value) {
    if ($this->dbversion){
      return $this->db->findprop($this->idprop, "$name = ". dbquote($value));
    }
    
    foreach ($this->items as $id => $item) {
      if ($item[$name] == $value) {
        return $id;
      }
    }
    return false;
  }
  
  public function additem(array $item) {
    $id = $this->dbversion ? $this->db->add($item) : ++$this->autoid;
    $item[$this->idprop] = $id;
    $this->items[$id] = $item;
    if (!$this->dbversion) $this->save();
    $this->added($id);
    return $id;
  }
  
  public function delete($id) {
    if ($this->dbversion) $this->db->delete("$this->idprop = $id");
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      if (!$this->dbversion) $this->save();
      $this->deleted($id);
      return true;
    }
    return false;
  }
  
}//class

class titems_storage extends titems {
  
  public function load() {
    return tstorage::load($this);
  }
  
  public function save() {
    return tstorage::save($this);
  }
  
}//class

class tsingleitems extends titems {
  public static $instances;
  public $id;
  
  public static function singleinstance($class, $id = 0) {
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$class][$id]))     return self::$instances[$class][$id];
    $self = litepublisher::$classes->newinstance($class);
    self::$instances[$class][$id] = $self;
    $self->id = $id;
    $self->load();
    return $self;
  }
  
  public function load() {
    if (!isset($this->id)) return false;
    return parent::load();
  }
  
  public function free() {
    unset(self::$instances[get_class($this)][$this->id]);
  }
  
}//class

//item.class.php
class titem extends tdata {
  public static $instances;
  //public $id;
  
  public static function iteminstance($class, $id = 0) {
    $name = call_user_func_array(array($class, 'getinstancename'), array());
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$name][$id]))     return self::$instances[$name][$id];
    $self = litepublisher::$classes->newitem($name, $class, $id);
    return $self->loaddata($id);
  }
  
  public function loaddata($id) {
    $this->data['id'] = $id;
    if ($id != 0) {
      if (!$this->load()) {
        $this->free();
        return false;
      }
      self::$instances[$this->instancename][$id] = $this;
    }
    return $this;
  }
  
  public function free() {
    unset(self::$instances[$this->getinstancename()][$this->id]);
  }
  
  public function __construct() {
    parent::__construct();
    $this->data['id'] = 0;
  }
  
  public function __destruct() {
    $this->free();
  }
  
  public function __set($name, $value) {
    if (parent::__set($name, $value)) return true;
    return  $this->Error("Field $name not exists in class " . get_class($this));
  }
  
  public function setid($id) {
    if ($id != $this->id) {
      $name = $this->instancename;
      if (!isset(self::$instances)) self::$instances = array();
      if (!isset(self::$instances[$name])) self::$instances[$name] = array();
      $a = &self::$instances[$this->instancename];
      if (isset(   $a[$this->id])) unset($a[$this->id]);
      if (isset($a[$id])) $a[$id] = 0;
      $a[$id] = $this;
      $this->data['id'] = $id;
    }
  }
  
  public function request($id) {
    if ($id != $this->id) {
      $this->setid($id);
      if (!$this->load()) return 404;
    }
  }
  
  public static function deletedir($dir) {
    if (!@file_exists($dir)) return false;
    tfiler::delete($dir, true, true);
    @rmdir($dir);
  }
  
}

class titem_storage extends titem {
  
  public function getowner() {
    $this->error(sprintf('The "%s" no have owner', get_class($this)));
  }
  
  public function load() {
    $owner = $this->owner;
    if ($owner->itemexists($this->id)) {
      $this->data = &$owner->items[$this->id];
      return true;
    }
    return false;
  }
  
  public function save() {
    return $this->owner->save();
  }
  
}//class

//classes.class.php
if (!function_exists( 'spl_autoload_register' ) ) {
  function __autoload($class) {
    litepublisher::$classes->_autoload($class);
  }
}

class tclasses extends titems {
  public $classes;
  public $interfaces;
  public $remap;
  public $factories;
  public $instances;
  public $included_files;
  
  public static function i() {
    if (!isset(litepublisher::$classes)) {
      $class = __class__;
      litepublisher::$classes = new $class();
      litepublisher::$classes->instances[$class] = litepublisher::$classes;
    }
    return litepublisher::$classes;
  }
  
  public static function instance() {
    return self::i();
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'classes';
    $this->dbversion = false;
    $this->addevents('onnewitem', 'gettemplatevar');
    $this->addmap('classes', array());
    $this->addmap('interfaces', array());
    $this->addmap('remap', array());
    $this->addmap('factories', array());
    $this->instances = array();
    if (function_exists('spl_autoload_register')) spl_autoload_register(array($this, '_autoload'));
    $this->data['memcache'] = false;
    $this->data['revision_memcache'] = 1;
    $this->included_files = array();
  }
  
  public function load() {
    return tstorage::load($this);
  }
  
  public function save() {
    return tstorage::save($this);
  }
  
  public function getinstance($class) {
    if (!class_exists($class)) {
      $this->error("Class $class not found");
    }
    if (!isset($this->instances[$class])) {
      $this->instances[$class] = $this->newinstance($class);
    }
    return $this->instances[$class];
  }
  
  public function newinstance($class) {
    if (!empty($this->remap[$class])) $class = $this->remap[$class];
    return new $class();
    /*
    if (preg_match('/^(tcomments|toptions|tsite|targs|ttheme)$/', $class)) return new $class();
    return new tdebugproxy(new $class());
    */
  }
  
  public function newitem($name, $class, $id) {
    if (!empty($this->remap[$class])) $class = $this->remap[$class];
    $this->callevent('onnewitem', array($name, &$class, $id));
    return new $class();
  }
  
  public function __get($name) {
    if (isset($this->classes[$name])) return $this->getinstance($this->classes[$name]);
    if (isset($this->items[$name])) return $this->getinstance($name);
    $class = 't' . $name;
    if (isset($this->items[$class])) return $this->getinstance($class);
    return parent::__get($name);
  }
  
  public function add($class, $filename, $path = '') {
    if (isset($this->items[$class]) &&
    ($this->items[$class][0] == $filename) && ($this->items[$class][1] == $path)) return false;
    
    $this->lock();
    $m = $this->memcache;
    $this->memcache = false;
    $this->items[$class] = array($filename, $path);
    $instance = $this->getinstance($class);
    if (method_exists($instance, 'install')) $instance->install();
    $this->memcache = $m;
    if ($m) $this->revision_memcache++;
    $this->unlock();
    $this->added($class);
    return true;
  }
  
  public function delete($class) {
    if (!isset($this->items[$class])) return false;
    $this->lock();
    $m = $this->memcache;
    $this->memcache = false;
    
    if (class_exists($class)) {
      $instance = $this->getinstance($class);
      if (method_exists($instance, 'uninstall')) $instance->uninstall();
    }
    
    unset($this->items[$class]);
    
    $this->memcache = $m;
    if ($m) $this->revision_memcache++;
    $this->unlock();
    $this->deleted($class);
  }
  
  public function reinstall($class) {
    if (isset($this->items[$class])) {
      $this->lock();
      $item = $this->items[$class];
      $this->delete($class);
      $this->add($class, $item[0], $item[1]);
      $this->unlock();
    }
  }
  
  public function _autoload($class) {
    if ($filename = $this->getclassfilename($class)) {
      $this->include_file($filename);
      $this->included_files[$class] = $filename;
    }
  }
  
  public function include_file($filename) {
    if (!tfilestorage::$memcache || litepublisher::$debug  || !$this->memcache) {
      if (file_exists($filename)) require_once($filename);
      return;
    }
    
    if (in_array($filename, $this->included_files)) return;
    
    if ($s =  tfilestorage::$memcache->get($filename)) {
      $i = strpos($s, ';');
      $revision = substr($s, 0, $i);
      if ($revision == $this->revision_memcache) {
        eval(substr($s, $i + 1));
        return;
      }
      tfilestorage::$memcache->delete($filename);
    }
    
    if (file_exists($filename)) {
      $s = file_get_contents($filename);
      eval('?>' . $s);
      //strip php tag and copyright in head
      if (strbegin($s, '<?php')) $s = substr($s, 5);
      if (strend($s, '?>')) $s = substr($s, 0, -2);
      $s = trim($s);
      if (strbegin($s, '/*')) $s = substr($s, strpos($s, '*/') + 2);
      $s = $this->revision_memcache . ';' . ltrim($s);
      tfilestorage::$memcache->set($filename, $s, false, 3600);
    }
  }
  
  public function getclassfilename($class, $debug = false) {
    if (isset($this->items[$class])) {
      $item = $this->items[$class];
      $filename = (litepublisher::$debug || $debug) && isset($item[2]) ? $item[2] : $item[0];
      if (Empty($item[1])) {
        return litepublisher::$paths->lib . $filename;
      }
      $filename = trim($item[1], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
      //if (file_exists($filename))  return $filename;
      //may be is subdir?
      if (file_exists(litepublisher::$paths->plugins . $filename)) return litepublisher::$paths->plugins . $filename;
      if (file_exists(litepublisher::$paths->themes . $filename)) return litepublisher::$paths->themes . $filename;
      if  (file_exists(litepublisher::$paths->home . $filename)) return  litepublisher::$paths->home . $filename;
    }
    if (isset($this->interfaces[$class])) return litepublisher::$paths->lib . $this->interfaces[$class];
    return false;
  }
  
  public function exists($class) {
    return isset($this->items[$class]);
  }
  
  public function getfactory($instance) {
    foreach ($this->factories as $classname => $factory) {
      if (@is_a($instance, $classname)) return $this->getinstance($factory);
    }
  }
  
  public function getresourcedir($c) {
    $class = self::get_class_name($c);
    if (isset($this->included_files[$class])) {
      $dir = dirname($this->included_files[$class]);
    } else {
      $dir = dirname($this->getclassfilename($class));
    }
    
    return $dir . '/resource/';
  }
  
}//class

function getinstance($class) {
  return litepublisher::$classes->getinstance($class);
}

//options.class.php
class toptions extends tevents_storage {
  public $groupnames;
  public $parentgroups;
  public $group;
  public $idgroups;
  protected $_user;
  protected $_admincookie;
  public $gmt;
  public $errorlog;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'options';
    $this->addevents('changed', 'perpagechanged', 'onsave');
    unset($this->cache);
    $this->gmt = 0;
    $this->errorlog = '';
    $this->group = '';
    $this->idgroups = array();
    $this->addmap('groupnames', array());
    $this->addmap('parentgroups', array());
  }
  
  public function afterload() {
    parent::afterload();
    date_default_timezone_set($this->timezone);
    $this->gmt = date('Z');
    if (!defined('dbversion')) define('dbversion', true);
  }
  
  public function savemodified() {
    $result = tstorage::savemodified();
    $this->onsave($result);
    return $result;
  }
  
  public function __set($name, $value) {
    if (in_array($name, $this->eventnames)) {
      $this->addevent($name, $value['class'], $value['func']);
      return true;
    }
    
    if (method_exists($this, $set = 'set' . $name)) {
      $this->$set($value);
      return true;
    }
    
    if (!array_key_exists($name, $this->data)  || ($this->data[$name] != $value)) {
      $this->data[$name] = $value;
      if ($name == 'solt') $this->data['emptyhash'] = $this->hash('');
      $this->save();
      $this->dochanged($name, $value);
    }
    return true;
  }
  
  private function dochanged($name, $value) {
    if ($name == 'perpage') {
      $this->perpagechanged();
      $urlmap = turlmap::i();
      $urlmap->clearcache();
    } elseif ($name == 'cache') {
      $urlmap = turlmap::i();
      $urlmap->clearcache();
    } else {
      $this->changed($name, $value);
    }
  }
  
  public function delete($name) {
    if (array_key_exists($name, $this->data)) {
      unset($this->data[$name]);
      $this->save();
    }
  }
  
  public function getadmincookie() {
    if (is_null($this->_admincookie)) {
      return $this->_admincookie = $this->authenabled && isset($_COOKIE['litepubl_user_flag']) && ($_COOKIE['litepubl_user_flag'] == 'true');
    }
    return $this->_admincookie;
  }
  
  public function setadmincookie($val) {
    $this->_admincookie = $val;
  }
  
  public function getuser() {
    if (is_null($this->_user)) {
      $this->_user = $this->authenabled ? $this->authcookie() : false;
    }
    return $this->_user;
  }
  
  public function setuser($id) {
    $this->_user = $id;
  }
  
  public function authcookie() {
    return $this->authcookies(isset($_COOKIE['litepubl_user_id']) ? (int) $_COOKIE['litepubl_user_id'] : 0, isset($_COOKIE['litepubl_user']) ? (string) $_COOKIE['litepubl_user'] : '');
  }
  
  public function authcookies($iduser, $password) {
    if (!$iduser || !$password) return false;
    $password = $this->hash($password);
    if (    $password == $this->emptyhash) return false;
    if (!$this->finduser($iduser, $password)) return false;
    
    $this->_user = $iduser;
    $this->updategroup();
    return $iduser;
  }
  
  public function finduser($iduser, $cookie) {
    if ($iduser == 1) return $this->compare_cookie($cookie);
    if (!$this->usersenabled)  return false;
    
    $users = tusers::i();
    try {
      $item = $users->getitem($iduser);
    } catch (Exception $e) {
      return false;
    }
    
    if ('hold' == $item['status']) return false;
    return ($cookie == $item['cookie']) && (strtotime($item['expired']) > time());
  }
  
  private function compare_cookie($cookie) {
    return !empty($this->cookiehash) && ($this->cookiehash == $cookie) && ($this->cookieexpired > time());
  }
  
  public function emailexists($email) {
    if (!$email) return false;
    if (!$this->authenabled) return false;
    if ($email == $this->email) return 1;
    if(!$this->usersenabled) return false;
    return tusers::i()->emailexists($email);
  }
  
  public function auth($email, $password) {
    if (!$this->authenabled) return false;
    if (!$email && !$password) return $this->authcookie();
    return $this->authpassword($this->emailexists($email), $password);
  }
  
  public function authpassword($iduser, $password) {
    if (!$iduser) return false;
    if ($iduser == 1) {
      if ($this->data['password'] != $this->hash($password))  return false;
    } else {
      if (!tusers::i()->authpassword($iduser, $password)) return false;
    }
    
    $this->_user = $iduser;
    $this->updategroup();
    return $iduser;
  }
  
  public function updategroup() {
    if ($this->_user == 1) {
      $this->group = 'admin';
      $this->idgroups = array(1);
    } else {
      $user = tusers::i()->getitem($this->_user);
      $this->idgroups = $user['idgroups'];
      $this->group = count($this->idgroups) ? tusergroups::i()->items[$this->idgroups[0]]['name'] : '';
    }
  }
  
  public function can_edit($idauthor) {
    return ($idauthor == $this->user) || ($this->group == 'admin') || ($this->group == 'editor');
  }
  
  public function getpassword() {
    if ($this->user <= 1) return $this->data['password'];
    $users = tusers::i();
    return $users->getvalue($this->user, 'password');
  }
  
  public function changepassword($newpassword) {
    $this->data['password'] = $this->hash($newpassword);
    $this->save();
  }
  
  public function getdbpassword() {
    if (function_exists('mcrypt_encrypt')) {
      return self::decrypt(    $this->data['dbconfig']['password'], $this->solt . litepublisher::$secret);
    } else {
      return str_rot13(base64_decode($this->data['dbconfig']['password']));
    }
  }
  
  public function setdbpassword($password) {
    if (function_exists('mcrypt_encrypt')) {
      $this->data['dbconfig']['password'] = self::encrypt($password, $this->solt . litepublisher::$secret);
    } else {
      $this->data['dbconfig']['password'] = base64_encode(str_rot13 ($password));
    }
    
    $this->save();
  }
  
  public function logout() {
    $this->setcookies('', 0);
  }
  
  public function setcookie($name, $value, $expired) {
    setcookie($name, $value, $expired,  litepublisher::$site->subdir . '/', false, '', $this->securecookie);
  }
  
  public function setcookies($cookie, $expired) {
    $this->setcookie('litepubl_user_id', $cookie ? $this->_user : '', $expired);
    $this->setcookie('litepubl_user', $cookie, $expired);
    $this->setcookie('litepubl_user_flag', $cookie && ('admin' == $this->group) ? 'true' : '', $expired);
    
    if ($this->_user == 1) {
      $this->save_cookie($cookie, $expired);
    } else if ($this->_user) {
      tusers::i()->setcookie($this->_user, $cookie, $expired);
    }
  }
  
  public function Getinstalled() {
    return isset($this->data['email']);
  }
  
  public function settimezone($value) {
    if(!isset($this->data['timezone']) || ($this->timezone != $value)) {
      $this->data['timezone'] = $value;
      $this->save();
      date_default_timezone_set($this->timezone);
      $this->gmt = date('Z');
    }
  }
  
  public function save_cookie($cookie, $expired) {
    $this->data['cookiehash'] = $cookie ? $this->hash($cookie) : '';
    $this->cookieexpired = $expired;
    $this->save();
  }
  
  public function hash($s) {
    return basemd5((string) $s . $this->solt . litepublisher::$secret);
  }
  
  public function ingroup($groupname) {
    //admin has all rights
    if ($this->user == 1) return true;
    if (in_array($this->groupnames['admin'], $this->idgroups)) return true;
    if (!$groupname) return true;
    $groupname = trim($groupname);
    if ($groupname == 'admin') return false;
    if (!isset($this->groupnames[$groupname])) $this->error(sprintf('The "%s" group not found', $groupname));
    $idgroup = $this->groupnames[$groupname];
    return in_array($idgroup, $this->idgroups);
  }
  
  public function ingroups(array $idgroups) {
    if ($this->ingroup('admin')) return true;
    return count(array_intersect($this->idgroups, $idgroups));
  }
  
  public function hasgroup($groupname) {
    if ($this->ingroup($groupname)) return true;
    // if group is children of user groups
    $idgroup = $this->groupnames[$groupname];
    if (!isset($this->parentgroups[$idgroup])) return false;
    return count(array_intersect($this->idgroups, $this->parentgroups[$idgroup]));
  }
  
  public function handexception($e) {
    $log = "Caught exception:\r\n" . $e->getMessage() . "\r\n";
    $trace = $e->getTrace();
    foreach ($trace as $i => $item) {
      if (isset($item['line'])) {
        $log .= sprintf('#%d %d %s ', $i, $item['line'], $item['file']);
      }
      
      if (isset($item['class'])) {
        $log .= $item['class'] . $item['type'] . $item['function'];
      } else {
        $log .= $item['function'];
      }
      
      if (isset($item['args']) && count($item['args'])) {
        $args = array();
        foreach ($item['args'] as $arg) {
          $args[] = self::var_export($arg);
        }
        
        $log .= "\n";
        $log .= implode(', ', $args);
      }
      
      $log .= "\n";
    }
    
    $log = str_replace(litepublisher::$paths->home, '', $log);
    $this->errorlog .= str_replace("\n", "<br />\n", htmlspecialchars($log));
    tfiler::log($log, 'exceptions.log');
    
    if (!(litepublisher::$debug || $this->echoexception || $this->admincookie || litepublisher::$urlmap->adminpanel)) {
      tfiler::log($log, 'exceptionsmail.log');
    }
  }
  
  public function trace($msg) {
    try {
      throw new Exception($msg);
    } catch (Exception $e) {
      $this->handexception($e);
    }
  }
  
  public function showerrors() {
    if (!empty($this->errorlog) && (litepublisher::$debug || $this->echoexception || $this->admincookie || litepublisher::$urlmap->adminpanel)) {
      echo $this->errorlog;
    }
  }
  
  public static function var_export(&$v) {
    switch(gettype($v)) {
      case 'string':
      return "'$v'";
      
      case 'object':
      return get_class($v);
      
      case 'boolean':
      return $v ? 'true' : 'false';
      
      case 'integer':
      case 'double':
      case 'float':
      return $v;
      
      case 'array':
      $result = "array (\n";
      foreach ($v as $k => $item) {
        $s = self::var_export($item);
        $result .= "$k = $s;\n";
      }
      $result .= ")\n";
      return $result;
      
      default:
      return gettype($v);
    }
  }
  
}//class

//site.class.php
class tsite extends tevents_storage {
  public $mapoptions;
  private $users;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'site';
    $this->addmap('mapoptions', array(
    'version' => 'version',
    'language' => 'language',
    ));
  }
  
  public function __get($name) {
    if (isset($this->mapoptions[$name])) {
      $prop = $this->mapoptions[$name];
      if (is_array($prop)) {
        list($classname, $method) = $prop;
        return call_user_func_array(array(getinstance($classname), $method), array($name));
      }
      
      return litepublisher::$options->data[$prop];
    }
    
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if ($name == 'url') return $this->seturl($value);
    if (in_array($name, $this->eventnames)) {
      $this->addevent($name, $value['class'], $value['func']);
    } elseif (isset($this->mapoptions[$name])) {
      $prop = $this->mapoptions[$name];
    if (is_string($prop)) litepublisher::$options->{$prop} = $value;
    } elseif (!array_key_exists($name, $this->data)  || ($this->data[$name] != $value)) {
      $this->data[$name] = $value;
      $this->save();
    }
    return true;
  }
  
  public function geturl() {
    if ($this->fixedurl) return $this->data['url'];
    return 'http://'. litepublisher::$domain;
  }
  
  public function getfiles() {
    if ($this->fixedurl) return $this->data['files'];
    return 'http://'. litepublisher::$domain;
  }
  
  public function seturl($url) {
    $url = rtrim($url, '/');
    $this->data['url'] = $url;
    $this->data['files'] = $url;
    $this->subdir = '';
    if ($i = strpos($url, '/', 10)) {
      $this->subdir = substr($url, $i);
    }
    $this->save();
  }
  
  public function getdomain() {
    return litepublisher::$domain;
  }
  
  public function getuserlink() {
    if ($id = litepublisher::$options->user) {
      if (!isset($this->users)) $this->users = array();
      if (isset($this->users[$id])) return $this->users[$id];
      $item = tusers::i()->getitem($id);
      if ($item['website']) {
        $result = sprintf('<a href="%s">%s</a>', $item['website'], $item['name']);
      } else {
        $page = $this->getdb('userpage')->getitem($id);
        if(intval($page['idurl'])) {
          $result = sprintf('<a href="%s%s">%s</a>', $this->url, litepublisher::$urlmap->getvalue($page['idurl'], 'url'), $item['name']);
        } else {
          $result = $item['name'];
        }
      }
      $this->users[$id] = $result;
      return $result;
    }
    return '';
  }
  
}//class

//urlmap.class.php
class turlmap extends titems {
  public $host;
  public $url;
  public $page;
  public $uripath;
  public $itemrequested;
  public  $context;
  public $cache_enabled;
  public $argtree;
  public $is404;
  public $isredir;
  public $adminpanel;
  public $mobile;
  protected $close_events;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    parent::__construct();
    if (tfilestorage::$memcache) {
      $this->cache = new tlitememcache($this);
    } else {
      $this->cache = new tfilecache();
    }
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->table = 'urlmap';
    $this->basename = 'urlmap';
    $this->addevents('beforerequest', 'afterrequest', 'onclearcache');
    $this->data['revision'] = 0;
    $this->data['disabledcron'] = false;
    $this->data['redirdom'] = false;
    $this->is404 = false;
    $this->isredir = false;
    $this->adminpanel = false;
    $this->mobile= false;
    $this->cache_enabled =     litepublisher::$options->cache && !litepublisher::$options->admincookie;
    $this->page = 1;
    $this->close_events = array();
  }
  
  protected function prepareurl($host, $url) {
    $this->host = $host;
    $this->page = 1;
    $this->uripath = array();
    if (litepublisher::$site->q == '?') {
      $this->url = substr($url, strlen(litepublisher::$site->subdir));
    } else {
      $this->url = $_GET['url'];
    }
  }
  
  public function request($host, $url) {
    $this->prepareurl($host, $url);
    $this->adminpanel = strbegin($this->url, '/admin/') || ($this->url == '/admin');
    if ($this->redirdom) {
      $parsedurl = parse_url(litepublisher::$site->url . '/');
      if ($host != strtolower($parsedurl['host'])) {
        return $this->redir($url);
      }
    }
    
    $this->beforerequest();
    if (!litepublisher::$debug && litepublisher::$options->ob_cache) ob_start();
    try {
      $this->dorequest($this->url);
    } catch (Exception $e) {
      litepublisher::$options->handexception($e);
    }
    
    if (!litepublisher::$debug && litepublisher::$options->ob_cache) {
      litepublisher::$options->showerrors();
      litepublisher::$options->errorlog = '';
      $afterclose = $this->isredir || count($this->close_events);
      if ($afterclose) $this->close_connection();
      while (@ob_end_flush ());
      flush();
      //prevent output while client connected
      if ($afterclose) ob_start();
    }
    $this->afterrequest($this->url);
    $this->close();
  }
  
  public function close_connection() {
    ignore_user_abort(true);
    //$len = $this->isredir ? 0 : ob_get_length();
    $len = ob_get_length();
    header('Connection: close');
    header('Content-Length: ' . $len);
    header('Content-Encoding: none');
    //header('Accept-Ranges: bytes');
  }
  
  protected function dorequest($url) {
    //echo "'$url'<br>";
    $this->itemrequested = $this->finditem($url);
    if ($this->isredir) return;
    if ($this->itemrequested) {
      return $this->printcontent($this->itemrequested);
    } else {
      $this->notfound404();
    }
  }
  
  public function getidurl($id) {
    if (!isset($this->items[$id])) {
      $this->items[$id] = $this->db->getitem($id);
    }
    return $this->items[$id]['url'];
  }
  
  public function findurl($url) {
    if ($result = $this->db->finditem('url = '. dbquote($url))) return $result;
    return false;
  }
  
  public function urlexists($url) {
    return $this->db->findid('url = '. dbquote($url));
  }
  
  private function query($url) {
    if ($item = $this->db->getassoc('url = '. dbquote($url). ' limit 1')) {
      $this->items[$item['id']] = $item;
      return $item;
    }
    return false;
  }
  
  public function finditem($url) {
    if ($result = $this->query($url)) return $result;
    $srcurl = $url;
    if ($i = strpos($url, '?'))  $url = substr($url, 0, $i);
    if ('//' == substr($url, -2)) $this->redir(rtrim($url, '/') . '/');
    //extract page number
    if (preg_match('/(.*?)\/page\/(\d*?)\/?$/', $url, $m)) {
      if ('/' != substr($url, -1))  return $this->redir($url . '/');
      $url = $m[1];
      if ($url == '') $url = '/';
      $this->page = max(1, abs((int) $m[2]));
    }
    
    if ($result = $this->query($url)) {
      if (($this->page == 1) && ($result['type'] == 'normal') && ($srcurl != $result['url'])) return $this->redir($url);
      return $result;
    }
    $url = $url != rtrim($url, '/') ? rtrim($url, '/') : $url . '/';
    if ($result = $this->query($url)) {
      if ($this->page > 1) return $result;
      if ($result['type'] == 'normal') return $this->redir($url);
      return $result;
    }
    
    $this->uripath = explode('/', trim($url, '/'));
    //tree convert into argument
    $url = trim($url, '/');
    $j = -1;
    while($i = strrpos($url, '/', $j)) {
      if ($result = $this->query('/' . substr($url, 0, $i + 1))) {
        if ($result['type'] != 'tree') return false;
        $this->argtree = substr($url, $i +1);
        return $result;
      }
      $j = - (strlen($url) - $i + 1);
    }
    
    return false;
  }
  
  private function getcachefile(array $item) {
    switch ($item['type']) {
      case 'normal':
      return  sprintf('%s-%d.php', $item['id'], $this->page);
      
      case 'usernormal':
      return sprintf('%s-page-%d-user-%d.php', $item['id'], $this->page, litepublisher::$options->user);
      
      case 'userget':
      return sprintf('%s-page-%d-user%d-get-%s.php', $item['id'], $this->page, litepublisher::$options->user, md5($_SERVER['REQUEST_URI']));
      
      default: //get
      return sprintf('%s-%d-%s.php', $item['id'], $this->page, md5($_SERVER['REQUEST_URI']));
    }
  }
  
  private function include_file($fn) {
    if (tfilestorage::$memcache) {
      if ($s = $this->cache->get($fn)) {
        eval('?>' . $s);
        return true;
      }
      return false;
    }
    
    $filename = litepublisher::$paths->cache . $fn;
    if (file_exists($filename) &&
    ((filemtime ($filename) + litepublisher::$options->expiredcache - litepublisher::$options->filetime_offset) >= time())) {
      include($filename);
      return true;
    }
    
    return false;
  }
  
  private function  printcontent(array $item) {
    $options = litepublisher::$options;
    if ($this->cache_enabled) {
      if ($this->include_file($this->getcachefile($item))) return;
    }
    
    if (class_exists($item['class']))  {
      return $this->GenerateHTML($item);
    } else {
      //$this->deleteclass($item['class']);
      $this->notfound404();
    }
  }
  
  public function getidcontext($id) {
    $item = $this->getitem($id);
    return $this->getcontext($item);
  }
  
  public function getcontext(array $item) {
    $class = $item['class'];
    $parents = class_parents($class);
    if (in_array('titem', $parents)) {
      return call_user_func_array(array($class, 'i'), array($item['arg']));
    } else {
      return getinstance($class);
    }
  }
  
  protected function GenerateHTML(array $item) {
    $context = $this->getcontext($item);
    $this->context  = $context;
    
    //special handling for rss
    if (method_exists($context, 'request') && ($s = $context->request($item['arg']))) {
      switch ($s) {
        case 404: return $this->notfound404();
        case 403: return $this->forbidden();
      }
    } else {
      if ($this->isredir) return;
      $template = ttemplate::i();
      $s = $template->request($context);
    }
    
    eval('?>'. $s);
    if ($this->cache_enabled && $context->cache) {
      $this->cache->set($this->getcachefile($item), $s);
    }
  }
  
  public function notfound404() {
    $redir = tredirector::i();
    if ($url  = $redir->get($this->url)) {
      return $this->redir($url);
    }
    
    $this->is404 = true;
    $this->printclasspage('tnotfound404');
  }
  
  private function printclasspage($classname) {
    $cachefile = $classname . '.php';
    if ($this->cache_enabled) {
      if ($this->include_file($cachefile)) return;
    }
    
    $obj = getinstance($classname);
    $Template = ttemplate::i();
    $s = $Template->request($obj);
    eval('?>'. $s);
    
    if ($this->cache_enabled && $obj->cache) {
      $this->cache->set($cachefile, $result);
    }
  }
  
  public function forbidden() {
    $this->is404 = true;
    $this->printclasspage('tforbidden');
  }
  
  public function addget($url, $class) {
    return $this->add($url, $class, null, 'get');
  }
  
  public function add($url, $class, $arg, $type = 'normal') {
    if (empty($url)) $this->error('Empty url to add');
    if (empty($class)) $this->error('Empty class name of adding url');
    if (!in_array($type, array('normal','get','tree', 'usernormal', 'userget'))) $this->error(sprintf('Invalid url type %s', $type));
    
    if ($item = $this->db->finditem('url = ' . dbquote($url))) $this->error(sprintf('Url "%s" already exists', $url));
    $item= array(
    'url' => $url,
    'class' => $class,
    'arg' => (string) $arg,
    'type' => $type
    );
    $item['id'] = $this->db->add($item);
    $this->items[$item['id']] = $item;
    return $item['id'];
  }
  
  public function delete($url) {
    $url = dbquote($url);
    if ($id = $this->db->findid('url = ' . $url)) {
      $this->db->iddelete($id);
    } else {
      return false;
    }
    
    $this->clearcache();
    $this->deleted($id);
    return true;
  }
  
  public function deleteclass($class) {
    if ($items =
    $this->db->getitems("class = '$class'")) {
      $this->db->delete("class = '$class'");
      foreach ($items as $item) $this->deleted($item['id']);
    }
    $this->clearcache();
  }
  
  public function deleteitem($id) {
    if ($item = $this->db->getitem($id)) {
      $this->db->iddelete($id);
      $this->deleted($id);
    }
    $this->clearcache();
  }
  
  //for Archives
  public function GetClassUrls($class) {
    $res = $this->db->query("select url from $this->thistable where class = '$class'");
    return $this->db->res2id($res);
  }
  
  public function clearcache() {
    $this->cache->clear();
    $this->onclearcache();
  }
  
  public function setexpired($id) {
    if ($item = $this->getitem($id)) {
      $cache = $this->cache;
      $page = $this->page;
      for ($i = 1; $i <= 10; $i++) {
        $this->page = $i;
        $cache->delete($this->getcachefile($item));
      }
      $this->page = $page;
    }
  }
  
  public function setexpiredcurrent() {
    $this->cache->delete($this->getcachefile($this->itemrequested));
  }
  
  public function expiredclass($class) {
    $items = $this->db->getitems("class = '$class'");
    if (count($items) == 0) return;
    $cache = $this->cache;
    $page = $this->page;
    $this->page = 1;
    foreach ($items as $item) {
      $cache->delete($this->getcachefile($item));
    }
    $this->page = $page;
  }
  
  public function addredir($from, $to) {
    if ($from == $to) return;
    $Redir = tredirector::i();
    $Redir->add($from, $to);
  }
  
  public static function unsub($obj) {
    $self = self::i();
    $self->lock();
    $self->unbind($obj);
    $self->deleteclass(get_class($obj));
    $self->unlock();
  }
  
  public function setonclose(array $a) {
    if (count($a) == 0) return;
    $this->close_events[] = $a;
  }
  
  public function onclose() {
    $this->setonclose(func_get_args());
  }
  
  private function call_close_events() {
    foreach ($this->close_events as $a) {
      try {
        $c = array_shift($a);
        if (!is_callable($c)) {
          $c = array($c, array_shift($a));
        }
        call_user_func_array($c, $a);
      } catch (Exception $e) {
        litepublisher::$options->handexception($e);
      }
    }
    $this->close_events = array();
  }
  
  protected function close() {
    $this->call_close_events();
    if ($this->disabledcron) return;
    if (tfilestorage::$memcache) {
      $memcache = tfilestorage::$memcache;
      $k =litepublisher::$domain . ':lastpinged';
      $lastpinged = $memcache->get($k);
      if (!$lastpinged  || (time() > $lastpinged  + 3600)) {
        $memcache->set($k, time(), false, 3600);
        tcron::pingonshutdown();
      }else {
        $k =litepublisher::$domain . ':singlepinged';
        $singlepinged = $memcache->get($k);
        if ($singlepinged && (time() > $singlepinged  + 300)) {
          $memcache->delete($k);
          tcron::pingonshutdown();
        }
      }
    } elseif (time() > litepublisher::$options->crontime + 3600) {
      litepublisher::$options->crontime = time();
      tcron::pingonshutdown();
    }
  }
  
  public function redir($url, $status = 301) {
    litepublisher::$options->savemodified();
    $this->isredir = true;
    
    switch ($status) {
      case 301:
      header('HTTP/1.1 301 Moved Permanently', true, 301);
      break;
      
      case 302:
      header('HTTP/1.1 302 Found', true, 302);
      break;
      
      case 307:
      header('HTTP/1.1 307 Temporary Redirect', true, 307);
      break;
    }
    
    if (!strbegin($url, 'http://') && !strbegin($url, 'https://')) $url = litepublisher::$site->url . $url;
    header('Location: ' . $url);
  }
  
  public function seturlvalue($url, $name, $value) {
    if ($id = $this->urlexists($url)) {
      $this->setvalue($id, $name, $value);
    }
  }
  
  public function setidurl($id, $url) {
    $this->db->setvalue($id, 'url', $url);
    if (isset($this->items[$id])) $this->items[$id]['url'] = $url;
  }
  
  public function getnextpage() {
    $url = $this->itemrequested['url'];
    return litepublisher::$site->url . rtrim($url, '/') . '/page/' . ($this->page + 1) . '/';
  }
  
  public function getprevpage() {
    $url = $this->itemrequested['url'];
    if ($this->page <= 2) return url;
    return litepublisher::$site->url . rtrim($url, '/') . '/page/' . ($this->page - 1) . '/';
  }
  
  public static function htmlheader($cache) {
    return sprintf('<?php turlmap::sendheader(%s); ?>', $cache ? 'true' : 'false');
  }
  
  public static function nocache() {
    Header( 'Cache-Control: no-cache, must-revalidate');
    Header( 'Pragma: no-cache');
  }
  
  public static function sendheader($cache) {
    if (!$cache) self::nocache();
    header('Content-Type: text/html; charset=utf-8');
    header('Last-Modified: ' . date('r'));
    header('X-Pingback: ' . litepublisher::$site->url . '/rpc.xml');
  }
  
  public static function sendxml() {
    header('Content-Type: text/xml; charset=utf-8');
    header('Last-Modified: ' . date('r'));
    header('X-Pingback: ' . litepublisher::$site->url . '/rpc.xml');
    echo '<?xml version="1.0" encoding="utf-8" ?>';
  }
  
}//class

class tlitememcache {
  public $revision;
  public $prefix;
  
  public function __construct($urlmap) {
    $this->revision = &$urlmap->data['revision'];
    $this->prefix = litepublisher::$domain . ':cache:';
  }
  
  public function clear() {
    $this->revision++;
    litepublisher::$urlmap->save();
  }
  
  public function set($filename, $data) {
    tfilestorage::$memcache->set($this->prefix . $filename,
    serialize(array(
    'revision' => $this->revision,
    'time' => time(),
    'data' => $data
    )), false, 3600);
  }
  
  public function get($filename) {
    if ($s = tfilestorage::$memcache->get($this->prefix . $filename)) {
      $a = unserialize($s);
      if ($a['revision'] == $this->revision) {
        return $a['data'];
      } else {
        tfilestorage::$memcache->delete($this->prefix . $filename);
      }
    }
    return false;
  }
  
  public function delete($filename) {
    tfilestorage::$memcache->delete($this->prefix . $filename);
  }
  
  public function exists($filename) {
    return !!tfilestorage::$memcache->get($this->prefix . $filename);
  }
  
}//class

class tfilecache {
  
  public function clear() {
    $path = litepublisher::$paths->cache;
    if ( $h = @opendir($path)) {
      while(FALSE !== ($filename = @readdir($h))) {
        if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) continue;
        $file = $path. $filename;
        if (is_dir($file)) {
          tfiler::delete($file . DIRECTORY_SEPARATOR, true, true);
        } else {
          unlink($file);
        }
      }
      closedir($h);
    }
  }
  
  public function set($filename, $data) {
    $fn = litepublisher::$paths->cache . $filename;
    if (!is_string($data)) $data = serialize($data);
    file_put_contents($fn, $data);
    @chmod($fn, 0666);
  }
  
  public function get($filename) {
    $fn = litepublisher::$paths->cache . $filename;
    if (file_exists($fn)) return  file_get_contents($fn);
    return false;
  }
  
  public function delete($filename) {
    $fn = litepublisher::$paths->cache . $filename;
    if (file_exists($fn)) unlink($fn);
  }
  
  public function exists($filename) {
    return file_exists(litepublisher::$paths->cache . $filename);
  }
  
}//class

//interfaces.php
interface itemplate {
  public function request($arg);
  public function gettitle();
  public function getkeywords();
  public function getdescription();
  public function gethead();
  public function getcont();
  public function getidview();
  public function setidview($id);
}

interface iwidgets {
  public function getwidgets(array &$items, $sidebar);
  public function getsidebar(&$content, $sidebar);
}

interface iadmin {
  public function getcontent();
  public function processform();
}

interface iposts {
  public function add(tpost $post);
  public function edit(tpost $post);
  public function delete($id);
}

interface imenu {
  public function getcurrent();
}

//plugin.class.php
class tplugin extends tevents {
  
  protected function create() {
    parent::create();
    $this->basename=  'plugins' .DIRECTORY_SEPARATOR  . strtolower(get_class($this));
  }
  
}

//users.class.php
class tusers extends titems {
  public $grouptable;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->basename = 'users';
    $this->table = 'users';
    $this->grouptable = 'usergroup';
    $this->addevents('beforedelete');
  }
  
  public function res2items($res) {
    if (!$res) return array();
    $result = array();
    $db = litepublisher::$db;
    while ($item = $db->fetchassoc($res)) {
      $id = (int) $item['id'];
      $item['idgroups'] = tdatabase::str2array($item['idgroups']);
      $result[] = $id;
      $this->items[$id] = $item;
    }
    return $result;
  }
  
  public function add(array $values) {
    return tusersman::i()->add($values);
  }
  
  public function edit($id, array $values) {
    return tusersman::i()->edit($id, $values);
  }
  
  public function setgroups($id, array $idgroups) {
    $idgroups = array_unique($idgroups);
    array_delete_value($idgroups, '');
    array_delete_value($idgroups, false);
    array_delete_value($idgroups, null);
    
    $this->items[$id]['idgroups'] = $idgroups;
    $db = $this->getdb($this->grouptable);
    $db->delete("iduser = $id");
    foreach ($idgroups as $idgroup) {
      $db->add(array(
      'iduser' => $id,
      'idgroup' => $idgroup
      ));
    }
  }
  
  public function delete($id) {
    if ($id == 1) return;
    $this->beforedelete($id);
    $this->getdb($this->grouptable)->delete('iduser = ' .(int)$id);
    tuserpages::i()->delete($id);
    $this->getdb('comments')->update("status = 'deleted'", "author = $id");
    return parent::delete($id);
  }
  
  public function emailexists($email) {
    if ($email == '') return false;
    if ($email == litepublisher::$options->email) return 1;
    
    foreach ($this->items as $id => $item) {
      if ($email == $item['email']) return $id;
    }
    
    if ($item = $this->db->finditem('email = '. dbquote($email))) {
      $id = intval($item['id']);
      $this->items[$id] = $item;
      return $id;
    }
    
    return false;
  }
  
  public function getpassword($id) {
    return $id == 1 ? litepublisher::$options->password : $this->getvalue($id, 'password');
  }
  
  public function changepassword($id, $password) {
    $item = $this->getitem($id);
    $this->setvalue($id, 'password', litepublisher::$options->hash($item['email'] . $password));
  }
  
  public function approve($id) {
    $this->setvalue($id, 'status', 'approved');
    $pages = tuserpages::i();
    if ($pages->createpage) $pages->addpage($id);
  }
  
  public function auth($email,$password) {
    return $this->authpassword($this->emailexists($email), $password);
  }
  
  public function authpassword($id,$password) {
    if (!$id || !$password) return false;
    $item = $this->getitem($id);
    if ($item['password'] == litepublisher::$options->hash($item['email']. $password)) {
      if ($item['status'] == 'wait') $this->approve($id);
      return $id;
    }
    return false;
  }
  
  public function authcookie($cookie) {
    $cookie = (string) $cookie;
    if (empty($cookie)) return false;
    $cookie = litepublisher::$options->hash( $cookie);
    if ($cookie == litepublisher::$options->hash('')) return false;
    if ($id = $this->findcookie($cookie)) {
      $item = $this->getitem($id);
      if (strtotime($item['expired']) > time()) return  $id;
    }
    return false;
  }
  
  public function findcookie($cookie) {
    $cookie = dbquote($cookie);
    if (($a = $this->select('cookie = ' . $cookie, 'limit 1')) && (count($a) > 0)) {
      return (int) $a[0];
    }
    return false;
  }
  
  public function getgroupname($id) {
    $item = $this->getitem($id);
    $groups = tusergroups::i();
    return $groups->items[$item['idgroups'][0]]['name'];
  }
  
  public function clearcookie($id) {
    $this->setcookie($id, '', 0);
  }
  
  public function setcookie($id, $cookie, $expired) {
    if ($cookie) $cookie = litepublisher::$options->hash($cookie);
    $expired = sqldate($expired);
    if (isset($this->items[$id])) {
      $this->items[$id]['cookie'] = $cookie;
      $this->items[$id]['expired'] = $expired;
    }
    
    $this->db->updateassoc(array(
    'id' => $id,
    'cookie' => $cookie,
    'expired' => $expired
    ));
  }
  
}//class

//items.pull.class.php
class tpullitems extends tdata {
  protected $perpull;
  protected $pull;
  protected $modified;
  protected $ongetitem;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'pullitems';
    $this->perpull = 20;
    $this->pull = array();
    $this->modified = array();
  }
  
  public function getitem($id) {
    if (isset($this->ongetitem)) return call_user_func_array($this->ongetitem, array($id));
    $this->error('Call abastract method getitem in class' . get_class($this));
  }
  
  public function getfilename($idpull) {
    return $this->basename . '.pull.' . $idpull;
  }
  
  public function loadpull($idpull) {
    if ($s = litepublisher::$urlmap->cache->get($this->getfilename($idpull))) {
      $this->pull[$idpull] = unserialize($s);
    } else {
      $this->pull[$idpull] = array();
    }
  }
  
  public function savepull($idpull) {
    if (!isset($this->modified[$idpull])) {
      litepublisher::$urlmap->onclose = array($this, 'savemodified', $idpull);
      $this->modified[$idpull] = true;
    }
  }
  
  public function savemodified($idpull) {
    litepublisher::$urlmap->cache->set($this->getfilename($idpull), serialize($this->pull[$idpull]));
  }
  
  public function getidpull($id) {
    $idpull = (int) floor ($id /$this->perpull);
    if (!isset($this->pull[$idpull])) $this->loadpull($idpull);
    return $idpull;
  }
  
  public function get($id) {
    $idpull = $this->getidpull($id);
    if (isset($this->pull[$idpull][$id])) return $this->pull[$idpull][$id];
    $result = $this->getitem($id);
    $this->pull[$idpull][$id] = $result;
    $this->savepull($idpull);
    return $result;
  }
  
  public function set($id, $item) {
    $idpull = $this->getidpull($id);
    $this->pull[$idpull][$id] = $item;
    $this->savepull($idpull);
  }
  
}//class

