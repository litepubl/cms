<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\core;

class UserOptions extends Items
{
    public $defvalues;
    private $defitems;

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'usersoptions';
        $this->table = 'useroptions';
        $this->addmap('defvalues', array());
        $this->defitems = array();
    }

    public function getVal($name)
    {
        return $this->getValue($this->getApp()->options->user, $name);
    }

    public function setVal($name, $value)
    {
        return $this->setValue($this->getApp()->options->user, $name, $value);
    }

    public function getItem($id)
    {
        $id = (int)$id;
        if (isset($this->items[$id]) || $this->select("$this->thistable.id = $id", 'limit 1')) {
            return $this->items[$id];
        }

        $item = $this->defvalues;
        $item['id'] = $id;
        $this->items[$id] = $item;
        $this->defitems[] = $id;
        return $item;
    }

    public function getValue($id, $name)
    {
        $item = $this->getItem($id);
        return $item[$name];
    }

    public function setValue($id, $name, $value)
    {
        $id = (int)$id;
        $item = $this->getitem($id);
        if ($value === $item[$name]) {
            return;
        }

        $this->items[$id][$name] = $value;
        $item[$name] = $value;
        $item['id'] = $id;
        $this->setItem($item);
    }

    public function setItem($item)
    {
        $this->items[(int)$item['id']] = $item;
        $i = array_search($item['id'], $this->defitems);
        if ($i === false) {
            $this->db->updateAssoc($item);
        } else {
            $this->db->insert($item);
            array_splice($this->defitems, $i, 1);
        }
    }
}
