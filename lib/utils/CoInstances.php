<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\utils;

trait CoInstances
{
    public $coclasses = [];
    public $coinstances = [];

    protected function createData()
    {
        parent::createData();

        if (method_exists($this, 'addMap')) {
                $this->addMap('coclasses', []);
        }
    }

    public function afterLoad()
    {
        parent::afterload();

        foreach ($this->coclasses as $coclass) {
            $instance = static ::iGet($coclass);
            if (method_exists($instance, 'afterLoad')) {
                        $instance->afterLoad();
            }

            $this->coinstances[] = $instance;
        }
    }

    protected function getProp(string $name)
    {
        foreach ($this->coinstances as $coinstance) {
            if (isset($coinstance->$name)) {
                return $coinstance->$name;
            }
        }

        return parent::getProp($name);
    }

    protected function setProp(string $name, $value)
    {
        foreach ($this->coinstances as $coinstance) {
            if (isset($coinstance->$name)) {
                $coinstance->$name = $value;
                return true;
            }
        }

        return parent::getProp($name);
    }

    public function __call($name, $params)
    {
        foreach ($this->coinstances as $coinstance) {
            if (method_exists($coinstance, $name) || $coinstance->method_exists($name)) {
                return call_user_func_array(
                    [
                    $coinstance,
                    $name
                    ],
                    $params
                );
            }
        }

        $this->error("The requested method $name not found in class " . get_class($this));
    }

    public function __isset($name)
    {
        if (parent::__isset($name)) {
                return true;
        }

        foreach ($this->coinstances as $coinstance) {
            if (isset($coinstance->$name)) {
                return true;
            }
        }

        return false;
    }

    public function coInstanceCall(string $method, array $args = [])
    {
        foreach ($this->coinstances as $coinstance) {
            if (method_exists($coinstance, $method)) {
                call_user_func_array([$coinstance, $method], $args);
            }
        }
    }

    public function free()
    {
        parent::free();
        $this->coInstanceCall('free');
    }

    private function indexofcoclass($class)
    {
        return array_search($class, $this->coclasses);
    }

    public function addCoClass(string $class)
    {
        if ($this->indexofcoclass($class) === false) {
            $this->coclasses[] = $class;
            $this->save();
            $this->coinstances = static ::iGet($class);
        }
    }

    public function deleteCoClass(string $class)
    {
        $i = $this->indexofcoclass($class);
        if (is_int($i)) {
            array_splice($this->coclasses, $i, 1);
            $this->save();
        }
    }
}
