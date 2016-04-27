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

class DB
{
use AppTrait;
use Singleton;

    public $mysqli;
    public $result;
    public $sql;
    public $dbname;
    public $table;
    public $prefix;
    public $history;

    public function __construct() {
        $this->sql = '';
        $this->table = '';
        $this->history = array();

        $this->setconfig($this->getconfig());
    }

    public function getConfig() {
        if (config::$db) {
            return config::$db;
        }

$options = $this->getApp()->options;
        if (isset( $options->dbconfig)) {
            $result =  $options->dbconfig;
            //decrypt db password
            $result['password'] =  $options->dbpassword;
            return $result;
        }

        return false;
    }

    public function setConfig($dbconfig) {
        if (!$dbconfig) {
 return false;
}


        $this->dbname = $dbconfig['dbname'];
        $this->prefix = $dbconfig['prefix'];

        $this->mysqli = new \mysqli($dbconfig['host'], $dbconfig['login'], $dbconfig['password'], $dbconfig['dbname'], $dbconfig['port'] > 0 ? $dbconfig['port'] : null);

        if (mysqli_connect_error()) {
            throw new \Exception('Error connect to database');
        }

        $this->mysqli->set_charset('utf8');
        //$this->query('SET NAMES utf8');

if (Config::$enableZeroDatetime ) {
$this->enableZeroDatetime();
}

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
    public function __get($name) {
if ($name == 'man') {
return DBManager::i();
}

        return $this->prefix . $name;
    }

    public function exec($sql) {
        return $this->query($sql);
    }

    public function query($sql) {
        $this->sql = $sql;
        if (Config::$debug) {
            $this->history[] = array(
                'sql' => $sql,
                'time' => 0
            );
            $microtime = microtime(true);
        }

        if (is_object($this->result)) {
$this->result->close();
}

        $this->result = $this->mysqli->query($sql);
        if ($this->result == false) {
            $this->logError($this->mysqli->error);
        } elseif (Config::$debug) {
            $this->history[count($this->history) - 1]['time'] = microtime(true) - $microtime;
            if ($this->mysqli->warning_count && ($r = $this->mysqli->query('SHOW WARNINGS'))) {
$this->getApp()->getLogger()->warning($sql, $r->fetch_assoc());
            }
}

        return $this->result;
    }

    protected function logError($mesg) {
        $log = "exception:\n$mesg\n$this->sql\n";
$app = $this->getApp();
$log .= $app->getLogManager()->getTrace();
        $log.= $this->performance();
die($log);
//$app->getLogger()->alert($log);
throw new \Exception($log);
}

    public function performance() {
        $result = '';
        $total = 0.0;
        $max = 0.0;
        foreach ($this->history as $i => $item) {
            $result.= "$i: {$item['time']}\n{$item['sql']}\n\n";
            $total+= $item['time'];
            if ($max < $item['time']) {
                $maxsql = $item['sql'];
                $max = $item['time'];
            }
        }
        $result.= "maximum $max\n$maxsql\n";
        $result.= sprintf("%s total time\n%d querries\n\n", $total, count($this->history));
        return $result;
    }

    public function quote($s) {
        return sprintf('\'%s\'', $this->mysqli->real_escape_string($s));
    }

    public function escape($s) {
        return $this->mysqli->real_escape_string($s);
    }

    public function setTable($table) {
        $this->table = $table;
        return $this;
    }

    public function select($where) {
        if ($where != '') $where = 'where ' . $where;
        return $this->query("SELECT * FROM $this->prefix$this->table $where");
    }

    public function idselect($where) {
        return $this->res2id($this->query("select id from $this->prefix$this->table where $where"));
    }

    public function selectassoc($sql) {
        return $this->query($sql)->fetch_assoc();
    }

    public function getAssoc($where) {
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
                $value = $value ? '1' : '0';
                $list[] = sprintf('%s = %s ', $name, $value);
                {
 continue;
}


            }

            $list[] = sprintf('%s = %s', $name, $this->quote($value));
        }

        return implode(', ', $list);
    }

    public function updateassoc(array $a, $index = 'id') {
        $id = $a[$index];
        unset($a[$index]);
        return $this->update($this->assoc2update($a) , "$index = '$id' limit 1");
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
        if ($id = $this->mysqli->insert_id) {
 return $id;
}


        $r = $this->query('select last_insert_id() from ' . $this->prefix . $this->table)->fetch_row();
        return (int)$r[0];
    }

    public function insert(array $a) {
        $this->insertrow($this->assoctorow($a));
    }

    public function assoctorow(array $a) {
        $vals = array();
        foreach ($a as $val) {
            if (is_bool($val)) {
                $vals[] = $val ? '1' : '0';
            } else {
                $vals[] = $this->quote($val);
            }
        }
        return sprintf('(%s) values (%s)', implode(', ', array_keys($a)) , implode(', ', $vals));
    }

    public function getCount($where = '') {
        $sql = "SELECT COUNT(*) as count FROM $this->prefix$this->table";
        if ($where) $sql.= ' where ' . $where;
        if (($res = $this->query($sql)) && ($r = $res->fetch_assoc())) {
            return (int)$r['count'];
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
        return $this->delete('id in (' . implode(', ', $items) . ')');
    }

    public function idexists($id) {
        if ($r = $this->query("select id  from $this->prefix$this->table where id = $id limit 1")) {
            return $r && $r->fetch_assoc();
        }

        return false;
    }

    public function exists($where) {
        return $this->query("select *  from $this->prefix$this->table where $where limit 1")->num_rows;
    }

    public function getList(array $list) {
        return $this->res2assoc($this->select(sprintf('id in (%s)', implode(',', $list))));
    }

    public function getItems($where) {
        return $this->res2assoc($this->select($where));
    }

    public function getItem($id, $propname = 'id') {
        if ($r = $this->query("select * from $this->prefix$this->table where $propname = $id limit 1")) {
 return $r->fetch_assoc();
}


        return false;
    }

    public function finditem($where) {
        return $this->query("select * from $this->prefix$this->table where $where limit 1")->fetch_assoc();
    }

    public function findid($where) {
        return $this->findprop('id', $where);
    }

    public function findprop($propname, $where) {
        if ($r = $this->query("select $propname from $this->prefix$this->table where $where limit 1")->fetch_assoc()) {
 return $r[$propname];
}


        return false;
    }

    public function getVal($table, $id, $name) {
        if ($r = $this->query("select $name from $this->prefix$table where id = $id limit 1")->fetch_assoc()) {
 return $r[$name];
}


        return false;
    }

    public function getValue($id, $name) {
        if ($r = $this->query("select $name from $this->prefix$this->table where id = $id limit 1")->fetch_assoc()) {
 return $r[$name];
}


        return false;
    }

    public function setValue($id, $name, $value) {
        return $this->update("$name = " . $this->quote($value) , "id = $id");
    }

    public function getValues($names, $where) {
        $result = array();
        $res = $this->query("select $names from $this->prefix$this->table where $where");
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
                $result[(int)$r['id']] = $r;
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
        return is_object($res) ? $res->num_rows : 0;
    }

public function enableZeroDatetime()
{
//use mysqli to prevent strange warnings
        $v = $this->fetchassoc($this->mysqli->query('show variables like \'sql_mode\''));
$a = explode(',', $v['Value']);
$ex = ['NO_ZERO_IN_DATE', 'NO_ZERO_DATE'];
$a = array_diff($a, $ex);
$v = implode(',', $a);
$this->mysqli->query("set sql_mode = '$v'");
}
}