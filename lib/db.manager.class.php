<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdbmanager  {
  public $engine;
  private $max_allowed_packet;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function __get($name) {
    if ($name == 'db') return litepublisher::$db;
    return litepublisher::$db->$name;
  }
  
  public function __call($name, $arg) {
    return call_user_func_array(array(litepublisher::$db, $name), $arg);
  }
  
  public function createtable($name, $struct) {
    //    if (litepublisher::$debug)
    if (!$this->engine) $this->engine = 'MyISAM'; //InnoDB
    $this->deletetable($name);
    return $this->exec("create table $this->prefix$name
    ($struct)
    ENGINE=$this->engine
    DEFAULT CHARSET=utf8
    COLLATE = utf8_general_ci");
  }
  
  public function deletetable($name) {
    //$this->exec("DROP TABLE IF EXISTS $this->prefix$name");
    if ($this->table_exists($name)) $this->exec("DROP TABLE $this->prefix$name");
  }
  
  public function  deletealltables( ) {
    $list = $this->res2array($this->query("show tables from " . litepublisher::$options->dbconfig['dbname']));
    foreach ($list as $row) {
      $this->exec("DROP TABLE IF EXISTS ". $row[0]);
    }
  }
  
  public function clear($name) {
    return $this->exec("truncate $this->prefix$name");
  }
  
  public function alter($table, $arg) {
    return $this->exec("alter table $this->prefix$table $arg");
  }
  
  public function getautoincrement($table) {
    $a = $this->fetchassoc($this->query("SHOW TABLE STATUS like '$this->prefix$table'"));
    return $a['Auto_increment'];
  }
  
  public function setautoincrement($table, $value) {
    $this->exec("ALTER TABLE $this->prefix$table AUTO_INCREMENT = $value");
  }
  
  public function getenum($table, $column) {
    if ($res = $this->query("describe $this->prefix$table $column")) {
      $r = $this->fetchassoc($res);
      $s = $r['Type'];
      if (preg_match('/enum\((.*?)\)/', $s, $m)) {
        $values = $m[1];
        $result = explode(',', $values);
        foreach ($result as $i => $v) {
          $result[$i] =trim($v, ' \'"');
        }
        return $result;
      }
    }
    return false;
  }
  
  public function setenum($table, $column, array $enum) {
    foreach ($enum as $i => $item) {
      $enum[$i] = sprintf('\'%s\'', trim($item));
    }
    $items = implode(',', $enum);
    $tmp = $column . '_tmp';
    $this->exec("alter table $this->prefix$table add $tmp enum($items)");
    $this->exec("update $this->prefix$table set $tmp = $column + 0");
    $this->exec("alter table $this->prefix$table drop $column");
    $this->exec("alter table $this->prefix$table change $tmp $column enum($items)");
  }
  
  public function addenum($table, $column, $value) {
    if ($values = $this->getenum($table, $column)) {
      if (!in_array($value, $values))  {
        $values[] = $value;
        $this->setenum($table, $column, $values);
      }
    }
  }
  
  public function delete_enum($table, $column, $value) {
    if ($values = $this->getenum($table, $column)) {
      $value = trim($value, ' \'"');
      $i = array_search($value, $values);
      if (false === $i) return;
      unset($values[$i]);
      $default = $values[0];
      $this->exec("update $this->prefix$table set $column = '$default' where $column = '$value'");
      $items = implode("','", $values);
      $items = "'$items'";
      $tmp = $column . '_tmp';
      $this->exec("alter table $this->prefix$table add $tmp enum($items)");
      foreach ($values as $name) {
        $this->exec("update $this->prefix$table set $tmp = '$name' where $column = '$name'");
      }
      $this->exec("alter table $this->prefix$table drop $column");
      $this->exec("alter table $this->prefix$table change $tmp $column enum($items)");
    }
  }
  
  public function column_exists($table, $column) {
    return $this->query("SHOW COLUMNS FROM $this->prefix$table LIKE '$column'")->num_rows;
  }
  
  public function key_exists($table, $key) {
    return $this->query("SHOW index FROM $this->prefix$table where Key_name = '$key'")->num_rows;
  }
  
  public function getdatabases() {
    if ($res = $this->query("show databases")) {
      return $this->res2id($res);
    }
    return false;
  }
  
  public function dbexists($name) {
    if ($list = $this->GetDatabaseList()) {
      return in_array($name, $list);
    }
    return FALSE;
  }
  
  public function gettables() {
    if ($res = $this->query(sprintf("show tables from %s like '%s%%'", litepublisher::$options->dbconfig['dbname'], litepublisher::$options->dbconfig['prefix']))) {
      return $this->res2id($res);
    }
    return false;
  }
  
  public function  table_exists( $name) {
    if ( $list = $this->gettables()) {
      return in_array($this->prefix . $name, $list);
    }
    return false;
  }
  
  public function createdatabase($name) {
    if ( $this->dbexists($name) )  return false;
    return $this->exec("CREATE DATABASE $name");
  }
  
  public function optimize() {
    $prefix = strtolower(litepublisher::$options->dbconfig['prefix']);
    $tables = $this->gettables();
    foreach ($tables as $table) {
      if (strbegin(strtolower($table), $prefix)) {
        $this->exec("LOCK TABLES `$table` WRITE");
        $this->exec("OPTIMIZE TABLE $table");
        $this->exec("UNLOCK TABLES");
      }
    }
  }
  
  public function export() {
    $options = litepublisher::$options;
    $v = $this->fetchassoc($this->query("show variables like 'max_allowed_packet'"));
    $this->max_allowed_packet =floor($v['Value']*0.8);
    
    $result = "-- Lite Publisher dump $options->version\n";
    $result .= "-- Datetime: ".date('Y-m-d H:i:s') . "\n";
  $result .= "-- Host: {$options->dbconfig['host']}\n";
  $result .= "-- Database: {$options->dbconfig['dbname']}\n\n";
    //$result .= "/*!40030 SET max_allowed_packet=$this->max_allowed_packet */;\n\n";
    $result .= "/*!40101 SET NAMES utf8 */;\n\n";
    
    $tables = $this->gettables();
    foreach ($tables as $table) {
      $result .= $this->exporttable($table);
    }
    $result .= "\n-- Lite Publisher dump end\n";
    return $result;
  }
  
  public function exporttable($name) {
    if ($row=$this->fetchnum($this->query("show create table `$name`"))) {
      $result = "DROP TABLE IF EXISTS `$name`;\n$row[1];\n\n";
      $res =$this->query("select * from `$name`");
      if ($this->countof($res) > 0) {
        $result .= "LOCK TABLES `$name` WRITE;\n/*!40000 ALTER TABLE `$name` DISABLE KEYS */;\n";
        $sql = '';
        while ($row = $this->fetchnum($res)) {
          $values= array();
          foreach($row as $v){
            $values[] = is_null($v) ? 'NULL' : $this->quote($v);
          }
          $sql .= $sql ? ',(' : '(';
          $sql .= implode(', ', $values);
          $sql .= ')';
          
          if (strlen($sql)>$this->max_allowed_packet) {
            $result .= "INSERT INTO `$name` VALUES ". $sql . ";\n";
            $sql = '';
          }
        }
        
        if ($sql) $result .= "INSERT INTO `$name` VALUES ". $sql . ";\n";
        $result .= "/*!40000 ALTER TABLE `$name` ENABLE KEYS */;\nUNLOCK TABLES;\n\n";
      }
      return $result;
    }
  }
  
  public function import(&$dump) {
    $sql = '';
    $i = 0;
    while ($j = strpos($dump, "\n", $i)) {
      $s = substr($dump, $i, $j - $i);
      $i = $j + 1;
      if ($this->iscomment($s)) continue;
      $sql .= $s . "\n";
      if ($s[strlen($s) - 1] != ';') continue;
      litepublisher::$db->exec($sql);
      $sql = '';
    }
    
    $s = substr($dump, $i);
    if (!$this->iscomment($s))  $sql .= $s;
    if ($sql != '') litepublisher::$db->exec($sql);
  }
  
  private function iscomment(&$s) {
    if (strlen($s) <= 2) return true;
  $c2 = $s{1};
  switch ($s{0}) {
      case '/': return $c2 == '*';
      case '-': return $c2 == '-';
      case '#': return true;
    }
    return false;
  }
  
}//class