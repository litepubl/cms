<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\core;

class DBManager
{
        use AppTrait;
    use Singleton;

    public $engine;

    private $max_allowed_packet;

    public function __get($name)
    {
        if ($name == 'db') {
            return $this->getApp()->db;
        }
        
        return $this->getApp()->db->$name;
    }

    public function __call($name, $arg)
    {
        return call_user_func_array([$this->getApp()->db, $name], $arg);
    }

    public function createTable($name, $struct)
    {
        if (! $this->engine) {
            $this->engine = 'MyISAM'; // InnoDB
        }
        $this->deletetable($name);
        return $this->exec(
            "create table $this->prefix$name
    ($struct)
    ENGINE=$this->engine
    DEFAULT CHARSET=utf8
    COLLATE = utf8_general_ci"
        );
    }

    public function deleteTable($name)
    {
        if ($this->tableExists($name)) {
            $this->exec("DROP TABLE $this->prefix$name");
        }
    }

    public function deleteAllTables()
    {
        $list = $this->res2array($this->query("show tables from " . $this->dbname));
        foreach ($list as $row) {
            $this->exec("DROP TABLE IF EXISTS " . $row[0]);
        }
    }

    public function clear($name)
    {
        return $this->exec("truncate $this->prefix$name");
    }

    public function alter($table, $arg)
    {
        return $this->exec("alter table $this->prefix$table $arg");
    }

    public function getAutoIncrement($table)
    {
        $a = $this->fetchassoc($this->query("SHOW TABLE STATUS like '$this->prefix$table'"));
        return $a['Auto_increment'];
    }

    public function setAutoIncrement($table, $value)
    {
        $this->exec("ALTER TABLE $this->prefix$table AUTO_INCREMENT = $value");
    }

    public function getEnum($table, $column)
    {
        if ($res = $this->query("describe $this->prefix$table $column")) {
            $r = $this->fetchassoc($res);
            $s = $r['Type'];
            if (preg_match('/enum\((.*?)\)/i', $s, $m)) {
                $values = $m[1];
                $result = explode(',', $values);
                foreach ($result as $i => $v) {
                    $result[$i] = trim($v, ' \'"');
                }
                
                return $result;
            }
        }
        
        return false;
    }

    public function setEnum($table, $column, array $enum)
    {
        $items = $this->quoteArray($enum);
        $default = Str::quote($enum[0]);
        $tmp = $column . '_tmp';
        $this->exec("alter table $this->prefix$table add $tmp enum($items) default $default");
        $this->exec("update $this->prefix$table set $tmp = $column + 0");
        $this->exec("alter table $this->prefix$table drop $column");
        $this->exec("alter table $this->prefix$table change $tmp $column enum($items) default $default");
    }

    public function addEnum($table, $column, $value)
    {
        if (($values = $this->getenum($table, $column)) && ! in_array($value, $values)) {
            $values[] = $value;
            $this->setenum($table, $column, $values);
        }
    }

    public function deleteEnum($table, $column, $value)
    {
        if ($values = $this->getenum($table, $column)) {
            $value = trim($value, ' \'"');
            $i = array_search($value, $values);
            if (false === $i) {
                return;
            }
            
            array_splice($values, $i, 1);
            $default = $values[0];
            $this->exec("update $this->prefix$table set $column = '$default' where $column = '$value'");
            
            $items = $this->quoteArray($values);
            $tmp = $column . '_tmp';
            $this->exec("alter table $this->prefix$table add $tmp enum($items)");
            foreach ($values as $name) {
                $this->exec("update $this->prefix$table set $tmp = '$name' where $column = '$name'");
            }
            $this->exec("alter table $this->prefix$table drop $column");
            $this->exec("alter table $this->prefix$table change $tmp $column enum($items)");
        }
    }

    public function renameEnum($table, $column, $oldvalue, $newvalue)
    {
        if (($oldvalue != $newvalue) && ($values = $this->getenum($table, $column))) {
            $oldvalue = trim($oldvalue, ' \'"');
            $newvalue = trim($newvalue, ' \'"');
            
            $i = array_search($oldvalue, $values);
            if (false !== $i) {
                $values[$i] = $newvalue;
                $items = $this->quoteArray($values);
                $default = Str::quote($values[0]);
                
                $tmp = $column . '_tmp';
                $this->exec("alter table $this->prefix$table add $tmp enum($items) default $default");
                // exclude changed
                unset($values[$i]);
                foreach ($values as $value) {
                    $value = Str::quote($value);
                    $this->exec("update $this->prefix$table set $tmp = $value where $column  = $value");
                }
                
                $oldvalue = Str::quote($oldvalue);
                $newvalue = Str::quote($newvalue);
                $this->exec("update $this->prefix$table set $tmp = $newvalue where $column  = $oldvalue");
                
                $this->exec("alter table $this->prefix$table drop $column");
                $this->exec("alter table $this->prefix$table change $tmp $column enum($items) default $default");
            }
        }
    }

    public function quoteArray(array $values)
    {
        foreach ($values as $i => $value) {
            $values[$i] = Str::quote(trim($value, ' \'"'));
        }
        
        return implode(', ', $values);
    }

    public function getVar($name)
    {
        $v = $this->fetchassoc($this->query("show variables like '$name'"));
        return $v['Value'];
    }

    public function setVar($name, $value)
    {
        $this->query("set $name = $value");
    }

    public function columnExists($table, $column)
    {
        return $this->query("SHOW COLUMNS FROM $this->prefix$table LIKE '$column'")->num_rows;
    }

    public function key_exists($table, $key)
    {
        return $this->query("SHOW index FROM $this->prefix$table where Key_name = '$key'")->num_rows;
    }

    public function deleteColumn(string $table, string $column)
    {
        $this->alter($table, "drop $column");
    }

    public function getDatabases()
    {
        if ($res = $this->query("show databases")) {
            return $this->res2id($res);
        }
        return false;
    }

    public function dbexists($name)
    {
        if ($list = $this->GetDatabaseList()) {
            return in_array($name, $list);
        }
        return false;
    }

    public function getTables()
    {
        if ($res = $this->query(sprintf("show tables from %s like '%s%%'", $this->dbname, $this->prefix))) {
            return $this->res2id($res);
        }
        return false;
    }

    public function tableExists($name)
    {
        if ($list = $this->gettables()) {
            return in_array($this->prefix . $name, $list);
        }
        return false;
    }

    public function createdatabase($name)
    {
        if ($this->dbexists($name)) {
            return false;
        }
        
        return $this->exec("CREATE DATABASE $name");
    }

    public function optimize()
    {
        $prefix = strtolower($this->prefix);
        $tables = $this->gettables();
        foreach ($tables as $table) {
            if (Str::begin(strtolower($table), $prefix)) {
                $this->exec("LOCK TABLES `$table` WRITE");
                $this->exec("OPTIMIZE TABLE $table");
                $this->exec("UNLOCK TABLES");
            }
        }
    }

    public function export()
    {
        //use mysqli  to prevent strange warning
        $v = $this->fetchassoc($this->mysqli->query("show variables like 'max_allowed_packet'"));
        $this->max_allowed_packet = floor($v['Value'] * 0.8);
        
        $result = "-- Lite Publisher dump\n";
        $result .= "-- Datetime: " . date('Y-m-d H:i:s') . "\n";
        $result .= "-- Host: $this->host\n";
        $result .= "-- Database: $this->dbname\n\n";
        $result .= "/*!40101 SET NAMES utf8 */;\n\n";
        
        $tables = $this->gettables();
        foreach ($tables as $table) {
            $result .= $this->exporttable($table);
        }
        $result .= "\n-- Lite Publisher dump end\n";
        return $result;
    }

    public function exportTable($name)
    {
        if ($row = $this->fetchnum($this->query("show create table `$name`"))) {
            $result = "DROP TABLE IF EXISTS `$name`;\n$row[1];\n\n";
            $res = $this->query("select * from `$name`");
            if ($this->countof($res) > 0) {
                $result .= "LOCK TABLES `$name` WRITE;\n/*!40000 ALTER TABLE `$name` DISABLE KEYS */;\n";
                $sql = '';
                while ($row = $this->fetchnum($res)) {
                    $values = array();
                    foreach ($row as $v) {
                        $values[] = is_null($v) ? 'NULL' : $this->quote($v);
                    }
                    $sql .= $sql ? ',(' : '(';
                    $sql .= implode(', ', $values);
                    $sql .= ')';
                    
                    if (strlen($sql) > $this->max_allowed_packet) {
                        $result .= "INSERT INTO `$name` VALUES " . $sql . ";\n";
                        $sql = '';
                    }
                }
                
                if ($sql) {
                    $result .= "INSERT INTO `$name` VALUES " . $sql . ";\n";
                }
                $result .= "/*!40000 ALTER TABLE `$name` ENABLE KEYS */;\nUNLOCK TABLES;\n\n";
            }
            return $result;
        }
    }

    public function import(&$dump)
    {
        $sql = '';
        $i = 0;
        while ($j = strpos($dump, "\n", $i)) {
            $s = substr($dump, $i, $j - $i);
            $i = $j + 1;
            if ($this->iscomment($s)) {
                continue;
            }
            
            $sql .= $s . "\n";
            if ($s[strlen($s) - 1] != ';') {
                continue;
            }
            
            $this->getApp()->db->exec($sql);
            $sql = '';
        }
        
        $s = substr($dump, $i);
        if (! $this->iscomment($s)) {
            $sql .= $s;
        }
        if ($sql != '') {
            $this->getApp()->db->exec($sql);
        }
    }

    private function iscomment(&$s)
    {
        if (strlen($s) <= 2) {
            return true;
        }
        
        $c2 = $s{1};
        switch ($s{0}) {
        case '/':
            return $c2 == '*';
            
        case '-':
            return $c2 == '-';
            
        case '#':
            return true;
        default:
            return false;
        }
    }
}