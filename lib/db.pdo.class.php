<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdatabase extends PDO {
  public $result;
  public $sql;
  public $table;
  public $prefix;
  public $history;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    if (!isset(litepublisher::$options->dbconfig)) return false;
    $dbconfig = litepublisher::$options->dbconfig;
    $this->table = '';
    $this->prefix =  $dbconfig['prefix'];
    $this->sql = '';
    $this->history = array();
    try {
      {
        $init =$dbconfig['port'] > 0 ?
"{$dbconfig['driver']}:host={$dbconfig['host']};port={$dbconfig['port']};dbname={$dbconfig['dbname']}" :
  "{$dbconfig['driver']}:host={$dbconfig['host']};dbname={$dbconfig['dbname']}";
      }
      parent::__construct($init, $dbconfig['login'], str_rot13(base64_decode($dbconfig['password'])),
      array(
      PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION       //ERRMODE_WARNING
      //PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL
      )
      );
    } catch (Exception $e) {
      die($e->getMessage());
    }
    //$this->exec('SET NAMES utf8');
    
    /* lost performance
    $timezone = date('Z') / 3600;
    if ($timezone > 0) $timezone = "+$timezone";
    $this->exec("SET time_zone = '$timezone:00'");
    */
  }
  
  public function __get ($name) {
    return $this->prefix . $name;
  }
  
  public function query($sql) {
    return $this->doquery($sql, true);
  }
  
  public function exec($sql) {
    return $this->doquery($sql, false);
  }
  
  private function doquery($sql, $isquery) {
    //if ($sql == $this->sql) return $this->result;
    $this->sql = $sql;
    if (litepublisher::$debug) {
      $this->history[] = array(
      'sql' => $sql,
      'started' => microtime(),
      'finished' => microtime()
      );
    }
    
    if (is_object ($this->result))  {
      $this->result->closeCursor();
    }
    try {
      if ($isquery) {
        $this->result = parent::query($sql);
      } else {
        $this->result = parent::exec($sql);
      }
      if (litepublisher::$debug) {
        $this->history[count($this->history) - 1]['finished'] = microtime();
      }
    } catch (Exception $e) {
      $this->doerror($e);
    }
    return $this->result;
  }
  
  private function doerror($e) {
    if (litepublisher::$debug) {
      $log = "exception:\n" . $e->getMessage();
      $log .= "\n$this->sql\n";
      $log .=str_replace(litepublisher::$paths->home, '', $e->getTraceAsString());
      $man = tdbmanager::i();
      $log .= $man->performance();
      $log = str_replace("\n", "<br />\n", htmlspecialchars($log));
      die($log);
    } else {
      litepublisher::$options->handexception($e);
    }
  }
  
  public function select($where) {
    if ($where != '') $where = 'where '. $where;
    return $this->query("SELECT * FROM $this->prefix$this->table $where");
  }
  
  public function idselect($where) {
    if($res = $this->query("select id from $this->prefix$this->table where $where")) {
      return $this->res2id($res);
    }
    return false;
  }
  
  public function selectassoc($sql) {
    if ($res = $this->query($sql)) {
      return $res->fetch(PDO::FETCH_ASSOC);
    }
    return false;
  }
  
  public function getassoc($where) {
    if ($res = $this->select($where)) {
      return $res->fetch(PDO::FETCH_ASSOC);
    }
    return false;
  }
  
  public function update($values, $where) {
    return $this->exec("update $this->prefix$this->table set " . $values  ." where $where");
  }
  
  public function idupdate($id, $values) {
    return $this->update($values, "id = $id");
  }
  
  public function updateassoc($a) {
    $list = array();
    foreach ($a As $name => $value) {
      if ($name == 'id') continue;
      $list[] = "$name = " . $this->quote($value);
    }
    
    return $this->update(implode(', ', $list), 'id = '. $a['id']);
  }
  
  public function UpdateProps($obj, $props) {
    $list = array();
    foreach ($props  As $name) {
      if ($name == 'id') continue;
      $list[] = "$Name = " . $this->quote($obj->$name);
    }
    
    return $this->update(implode(', ', $list), "id = $obj->id");
  }
  
  public function insertrow($row) {
    $this->exec(sprintf('INSERT INTO %s%s %s', $this->prefix, $this->table, $row));
  }
  
  public function insertassoc(array $a) {
    unset($a['id']);
    return $this->add($a);
  }
  
  public function insert(array $a) {
    if ($this->idexists($a['id'])) {
      $this->updateassoc($a);
    } else {
      $this->add($a);
    }
  }
  
  public function add(array $a) {
    $this->insertrow($this->assoctorow($a));
    return $this->lastInsertId();
  }
  
  public function insert_a(array $a) {
    $this->insertrow($this->assoctorow($a));
  }
  
  public function getcount($where = '') {
    $sql = "SELECT COUNT(*) as count FROM $this->prefix$this->table";
    if ($where != '') $sql .= ' where '. $where;
    if ($res = $this->query($sql)) {
      $r = $res->fetch(PDO::FETCH_ASSOC);
      return $r['count'];
    }
    return false;
  }
  
  public function delete($where) {
    return $this->exec("delete from $this->prefix$this->table where $where");
  }
  
  public function iddelete($id) {
    return $this->exec("delete from $this->prefix$this->table where id = $id");
  }
  
  public function deleteitems(array $items) {
    return $this->delete('id in ('. implode(', ', $items) . ')');
  }
  
  public function idexists($id) {
    if (($res = $this->query("select id  from $this->prefix$this->table where id = $id limit 1")) && ($r = $res->fetch()))  return true;
    return false;
  }
  
  public function  exists($where) {
    if (($res = $this->query("select *  from $this->prefix$this->table where $where limit 1")) && ($r = $res->fetch()))  return true;
    return false;
  }
  
  public function getlist(array $list) {
    if ($res = $this->select(sprintf('id in (%s)', implode(',', $list)))) {
      return $res->fetchAll(PDO::FETCH_ASSOC);
    }
    return false;
    
  }
  
  public function getitems($where) {
    if ($res = $this->select($where)) {
      return $res->fetchAll(PDO::FETCH_ASSOC);
    }
    return false;
  }
  
  public function getitem($id) {
    if ($res = $this->query("select * from $this->prefix$this->table where id = $id limit 1")) {
      return $res->fetch(PDO::FETCH_ASSOC);
    }
    return false;
  }
  
  public function finditem($where) {
    if ($res = $this->query("select * from $this->prefix$this->table where $where limit 1")) {
      return $res->fetch(PDO::FETCH_ASSOC);
    }
    return false;
  }
  
  public function findid($where) {
    if($res = $this->query("select id from $this->prefix$this->table where $where limit 1")) {
      if ($r = $res->fetch(PDO:: FETCH_NUM)) {
        return $r[0];
      }
    }
    return false;
  }
  
  public function getvalue($id, $name) {
    if ($res = $this->query("select $name from $this->prefix$this->table where id = $id limit 1")) {
      $r = $res->fetch(PDO::FETCH_ASSOC);
      return $r[$name];
    }
    return false;
  }
  
  public function setvalue($id, $name, $value) {
    return $this->update("$name = " . $this->quote($value), "id = $id");
  }
  
  public function res2array($res) {
    $result = array();
    //    while ($row = $res->fetch(PDO::FETCH_NUM)) {
      foreach ($res as $row) {
        $result[] = $row;
      }
      return $result;
    }
    
    public function res2id($res) {
      $result = array();
      $res->setFetchMode (PDO::FETCH_NUM);
      foreach ($res as $row) {
        $result[] = $row[0];
      }
      return $result;
    }
    
    public function res2assoc($res) {
      return $res ? $res->fetchAll(PDO::FETCH_ASSOC) : false;
    }
    
    public function fetchassoc($res) {
      return $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
    }
    
    public function fetchnum($res) {
      return $res ? $res->fetch(PDO::FETCH_NUM) : false;
    }
    
    public function countof($res) {
      return $res ? $res->rowCount() : false;
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
  ?>