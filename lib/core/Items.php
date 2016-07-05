<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 */

namespace litepubl\core;

class Items extends Events
{
    public $items;
    public $dbversion;
    protected $idprop;
    protected $autoid;

    protected function create()
    {
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

    public function getStorage()
    {
        if ($this->dbversion) {
            return $this->getApp()->poolStorage;
        } else {
            return parent::getStorage();
        }
    }

    public function loadall()
    {
        if ($this->dbversion) {
            return $this->select('', '');
        }
    }

    public function loaditems(array $items)
    {
        if (!$this->dbversion) {
            return;
        }

        //exclude loaded items
        $items = array_diff($items, array_keys($this->items));
        if (count($items) == 0) {
            return;
        }

        $list = implode(',', $items);
        $this->select("$this->thistable.$this->idprop in ($list)", '');
    }

    public function select(string $where, string $limit): array
    {
        if (!$this->dbversion) {
            $this->error('Select method must be called ffrom database version');
        }

        if ($where) {
            $where = 'where ' . $where;
        }

        return $this->res2items($this->db->query("SELECT * FROM $this->thistable $where $limit"));
    }

    public function res2items($res)
    {
        if (!$res) {
            return array();
        }

        $result = array();
        $db = $this->getApp()->db;
        while ($item = $db->fetchassoc($res)) {
            $id = $item[$this->idprop];
            $result[] = $id;
            $this->items[$id] = $item;
        }

        return $result;
    }

    public function findItem($where)
    {
        $a = $this->select($where, 'limit 1');
        return count($a) ? $a[0] : false;
    }

    public function getCount()
    {
        if ($this->dbversion) {
            return $this->db->getcount();
        } else {
            return count($this->items);
        }
    }

    public function getItem($id)
    {
        if (isset($this->items[$id])) {
            return $this->items[$id];
        }

        if ($this->dbversion && $this->select("$this->thistable.$this->idprop = $id", 'limit 1')) {
            return $this->items[$id];
        }

        return $this->error(sprintf('Item %d not found in class %s', $id, get_class($this)));
    }

    public function getValue($id, $name)
    {
        if ($this->dbversion && !isset($this->items[$id])) {
            $this->items[$id] = $this->db->getitem($id, $this->idprop);
        }

        return $this->items[$id][$name];
    }

    public function setValue($id, $name, $value)
    {
        $this->items[$id][$name] = $value;
        if ($this->dbversion) {
            $this->db->update("$name = " . Str::quote($value), "$this->idprop = $id");
        }
    }

    public function itemExists($id)
    {
        if (isset($this->items[$id])) {
            return true;
        }

        if ($this->dbversion) {
            try {
                return $this->getitem($id);
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    public function indexof($name, $value)
    {
        if ($this->dbversion) {
            return $this->db->findprop($this->idprop, "$name = " . Str::quote($value));
        }

        foreach ($this->items as $id => $item) {
            if ($item[$name] == $value) {
                return $id;
            }
        }
        return false;
    }

    public function addItem(array $item)
    {
        $id = $this->dbversion ? $this->db->add($item) : ++$this->autoid;
        $item[$this->idprop] = $id;
        $this->items[$id] = $item;
        if (!$this->dbversion) {
            $this->save();
        }

        $this->added($id);
        return $id;
    }

    public function delete($id)
    {
        if ($this->dbversion) {
            $this->db->delete("$this->idprop = $id");
        }

        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            if (!$this->dbversion) {
                $this->save();
            }

            $this->deleted($id);
            return true;
        }

        return false;
    }
}
