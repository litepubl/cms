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

/**
 * This is the base class to represent single item in collection
 *
 * @property int $id
 */

class Item extends Data
{
    public static $instances;

    public static function i($id = 0)
    {
        return static ::itemInstance(get_called_class(), (int)$id);
    }

    public static function itemInstance($class, $id = 0)
    {
        $name = $class::getInstanceName();
        if (!isset(static ::$instances)) {
            static ::$instances = [];
        }

        if (isset(static ::$instances[$name][$id])) {
            return static ::$instances[$name][$id];
        }

        $self = static ::getAppInstance()->classes->newItem($name, $class, $id);
        return $self->loadData($id);
    }

    public function loadData($id)
    {
        $this->data['id'] = $id;
        if ($id) {
            if (!$this->load()) {
                $this->free();
                return false;
            }

            static ::$instances[$this->instancename][$id] = $this;
        }

        return $this;
    }

    public function free()
    {
        unset(static ::$instances[$this->getinstancename() ][$this->id]);
    }

    public function __construct()
    {
        parent::__construct();
        $this->data['id'] = 0;
    }

    public function __destruct()
    {
        $this->free();
    }

    public function __set($name, $value)
    {
        if (parent::__set($name, $value)) {
            return true;
        }

        return $this->Error("Field $name not exists in class " . get_class($this));
    }

    public function setId($id)
    {
        if ($id != $this->id) {
            $name = $this->instanceName;
            if (!isset(static ::$instances)) {
                static ::$instances = array();
            }

            if (!isset(static ::$instances[$name])) {
                static ::$instances[$name] = array();
            }

            $a = & static ::$instances[$this->instanceName];
            if (isset($a[$this->id])) {
                unset($a[$this->id]);
            }

            if (isset($a[$id])) {
                $a[$id] = 0;
            }

            $a[$id] = $this;
            $this->data['id'] = $id;
        }
    }

    public function loadItem($id)
    {
        if ($id == $this->id) {
            return true;
        }

        $this->setid($id);
        if ($this->load()) {
            return true;
        }

        return false;
    }
}
