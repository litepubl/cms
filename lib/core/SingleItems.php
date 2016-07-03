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

class SingleItems extends Items
{
    public static $instances;
    public $copyprops;

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->copyprops = array();
    }

    public function addinstance($instance)
    {
        $classname = get_class($instance);
        $item = array(
            'classname' => $classname,
        );

        foreach ($this->copyprops as $prop) {
            $item[$prop] = $instance->{$prop};
        }

        $id = $this->additem($item);
        $instance->id = $id;
        $instance->save();

        if (isset(static ::$instances[$classname])) {
            static ::$instances[$classname][$id] = $instance;
        } else {
            static ::$instances[$classname] = array(
                $id => $instance
            );
        }

        return $id;
    }

    public function get($id)
    {
        $id = (int)$id;
        $classname = $this->items[$id]['classname'];
        $result = static ::iGet($classname);
        if ($id != $result->id) {
            if (!isset(static ::$instances[$classname])) {
                static ::$instances[$classname] = array();
            }

            if (isset(static ::$instances[$classname][$id])) {
                $result = static ::$instances[$classname][$id];
            } else {
                if ($result->id) {
                    $result = new $classname();
                }

                $result->id = $id;
                $result->load();
                static ::$instances[$classname][$id] = $result;
            }
        }

        return $result;
    }
}
