<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\update;

class miniman
{
    public $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function __get($name)
    {
        return $this->db->$name;
    }

    public function __call($name, $arg)
    {
        return call_user_func_array([$this->db, $name], $arg);
    }

    public function alter($table, $arg)
    {
        return $this->exec("alter table $this->prefix$table $arg");
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
        $default = $this->db->quote($enum[0]);
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
                $default = $this->db->quote($values[0]);
                
                $tmp = $column . '_tmp';
                $this->exec("alter table $this->prefix$table add $tmp enum($items) default $default");
                // exclude changed
                unset($values[$i]);
                foreach ($values as $value) {
                    $value = $this->db->quote($value);
                    $this->exec("update $this->prefix$table set $tmp = $value where $column  = $value");
                }
                
                $oldvalue = $this->db->quote($oldvalue);
                $newvalue = $this->db->quote($newvalue);
                $this->exec("update $this->prefix$table set $tmp = $newvalue where $column  = $oldvalue");
                
                $this->exec("alter table $this->prefix$table drop $column");
                $this->exec("alter table $this->prefix$table change $tmp $column enum($items) default $default");
            }
        }
    }

    public function quoteArray(array $values)
    {
        foreach ($values as $i => $value) {
            $values[$i] = $this->db->quote(trim($value, ' \'"'));
        }
        
        return implode(', ', $values);
    }

    public function columnExists($table, $column)
    {
        return $this->query("SHOW COLUMNS FROM $this->prefix$table LIKE '$column'")->num_rows;
    }

    public function getTables()
    {
        if ($res = $this->query(sprintf("show tables from %s like '%s%%'", $this->dbname, $this->prefix))) {
            return $this->res2id($res);
        }
        return false;
    }

    public function export()
    {
        $result = '';
        $tables = $this->gettables();
        foreach ($tables as $table) {
            $result .= $this->exporttable($table);
        }

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
                    $values = [];
                    foreach ($row as $v) {
                        $values[] = is_null($v) ? 'NULL' : $this->quote($v);
                    }
                    $sql .= $sql ? ',(' : '(';
                    $sql .= implode(', ', $values);
                    $sql .= ')';
                    
                }
                
                if ($sql) {
                    $result .= "INSERT INTO `$name` VALUES " . $sql . ";\n";
                }
                $result .= "/*!40000 ALTER TABLE `$name` ENABLE KEYS */;\nUNLOCK TABLES;\n\n";
            }
            return $result;
        }
    }

}
