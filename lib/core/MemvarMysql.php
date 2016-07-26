<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\core;

class MemvarMysql
{
    use appTrait;

    public $lifetime;
    public $table;
    public $data;
    private $checked;

    public function __construct()
    {
        $this->table = 'memstorage';
        $this->checked = false;
        $this->data = array();
        $this->lifetime = 10800;
    }

    public function getDb(): DB
    {
        return $this->getApp()->db;
    }

    public function getName(string $name): string
    {
        if (strlen($name) > 32) {
            return md5($name);
        }

        return $name;
    }

    public function __get($name)
    {
        $name = $this->getname($name);
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return $this->get($name);
    }

    public function get(string $name)
    {
        $result = false;
        if (!$this->checked) {
            $this->check();
        }

        $db = $this->getdb();
        if ($r = $db->query("select value from $db->prefix$this->table where name = '$name' limit 1")->fetch_assoc()) {
            $result = $this->unserialize($r['value']);
            $this->data[$name] = $result;
        }

        return $result;
    }

    public function __set($name, $value)
    {
        $name = $this->getname($name);
        $exists = isset($this->data[$name]);
        $this->data[$name] = $value;
        if (!$this->checked) {
            $this->check();
        }

        $db = $this->getdb();
        $v = $db->quote($this->serialize($value));
        if ($exists) {
            $db->query("update $db->prefix$this->table set value = $v where name = '$name' limit 1");
        } else {
            $db->query("insert into $db->prefix$this->table (name, value) values('$name', $v)");
        }
    }

    public function __unset($name)
    {
        $name = $this->getname($name);
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }

        if (!$this->checked) {
            $this->check();
        }

        $db = $this->getdb();
        $db->query("delete from $db->prefix$this->table where name = '$name' limit 1");
    }

    public function serialize($data): string
    {
        return serialize($data);
    }

    public function unserialize(string $data)
    {
        return unserialize($data);
    }

    public function check()
    {
        $this->checked = true;

        //exclude throw exception
        $db = $this->getdb();
        $res = $db->mysqli->query("select value from $db->prefix$this->table where name = 'created' limit 1");
        if (is_object($res) && ($r = $res->fetch_assoc())) {
            $res->close();
            $created = $this->unserialize($r['value']);
            if ($created + $this->lifetime < time()) {
                $this->loadAll();
                $this->clear();
                $this->data['created'] = time();
                $this->saveAll();
            }
        } else {
            $this->createTable();
            $this->created = time();
        }
    }

    public function loadAll()
    {
        $db = $this->getdb();
        $res = $db->query("select * from $db->prefix$this->table");
        if (is_object($res)) {
            while ($item = $res->fetch_assoc()) {
                $this->data[$item['name']] = $this->unserialize($item['value']);
            }
        }
    }

    public function saveAll()
    {
        $db = $this->getdb();
        $a = array();
        foreach ($this->data as $name => $value) {
            $a[] = sprintf('(\'%s\',%s)', $name, $db->quote($this->serialize($value)));
        }

        $values = implode(',', $a);
        $db->query("insert into $db->prefix$this->table (name, value) values $values");
    }

    public function createTable()
    {
        $db = $this->getdb();
        $db->mysqli->query(
            "create table if not exists $db->prefix$this->table (
    name varchar(32) not null,
    value varchar(255),
    key (name)
    )
    ENGINE=MEMORY
    DEFAULT CHARSET=utf8
    COLLATE = utf8_general_ci"
        );
    }

    public function clear()
    {
        $db = $this->getdb();
        try {
            $db->query("truncate table $db->prefix$this->table");
        } catch (\Exception $e) {
        }
    }
}
