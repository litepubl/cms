<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\post;

use litepubl\core\Str;

class Meta extends \litepubl\core\Item
{

    public static function getInstancename()
    {
        return 'postmeta';
    }

    protected function create()
    {
        $this->table = 'postsmeta';
    }

    public function getDbversion()
    {
        return true;
    }

    public function __set($name, $value)
    {
        if ($name == 'id') {
            return $this->setid($value);
        }

        $exists = isset($this->data[$name]);
        if ($exists && ($this->data[$name] == $value)) {
            return true;
        }

        $this->data[$name] = $value;
        $name = Str::quote($name);
        $value = Str::quote($value);
        if ($exists) {
            $this->db->update("value = $value", "id = $this->id and name = $name");
        } else {
            $this->db->insertrow("(id, name, value) values ($this->id, $name, $value)");
        }
    }

    public function __unset($name)
    {
        $this->remove($name);
    }

    //db
    public function load()
    {
        $this->LoadFromDB();
        return true;
    }

    protected function LoadFromDB()
    {
        $db = $this->db;
        $res = $db->select("id = $this->id");
        if (is_object($res)) {
            while ($r = $res->fetch_assoc()) {
                $this->data[$r['name']] = $r['value'];
            }
        }
        return true;
    }

    protected function SaveToDB()
    {
        $db = $this->db;
        $db->delete("id = $this->id");
        foreach ($this->data as $name => $value) {
            if ($name == 'id') {
                continue;
            }

            $name = Str::quote($name);
            $value = Str::quote($value);
            $this->db->insertrow("(id, name, value) values ($this->id, $name, $value)");
        }
    }

    public function remove($name)
    {
        if ($name == 'id') {
            return;
        }

        unset($this->data[$name]);
        $this->db->delete("id = $this->id and name = '$name'");
    }

    public static function loaditems(array $items)
    {
        if (!count($items)) {
            return;
        }

        //exclude already loaded items
        if (isset(static ::$instances['postmeta'])) {
            $items = array_diff($items, array_keys(static ::$instances['postmeta']));
            if (!count($items)) {
                return;
            }
        } else {
            static ::$instances['postmeta'] = array();
        }

        $instances = & static ::$instances['postmeta'];
        $db = static::getAppInstance()->db;
        $db->table = 'postsmeta';
        $res = $db->select(sprintf('id in (%s)', implode(',', $items)));
        while ($row = $db->fetchassoc($res)) {
            $id = (int)$row['id'];
            if (!isset($instances[$id])) {
                $instances[$id] = new self();
                $instances[$id]->data['id'] = $id;
            }

            $instances[$id]->data[$row['name']] = $row['value'];
        }

        return $items;
    }
}
