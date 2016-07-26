<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\utils;

trait TempProps
{
    private $props = [];

    protected function newProps()
    {
        return new class ($this) {
            private $owner;
            private $props = [];

            public function __construct($owner)
            {
                $this->owner = $owner;
            }

            public function __destruct()
            {
                foreach ($this->props as $name) {
                    $this->owner->removeTempProp($name);
                }
            }

            public function __set($name, $value)
            {
                $this->props[] = $name;
                $this->owner->addTempProp($name, $value);
            }
        };
    }

    protected function getProp(string $name)
    {
        if (isset($this->props[$name])) {
            return $this->props[$name];
        }

        return parent::getProp($name);
    }

    protected function setProp(string $name, $value)
    {
        if (isset($this->props[$name])) {
            $this->props[$name] = $value;
        } else {
            parent::getProp($name);
        }
    }

    public function addTempProp(string $name, $value)
    {
        $this->props[$name] = $value;
    }

    public function removeTempProp(string $name)
    {
        if (isset($this->props[$name])) {
            unset($this->props[$name]);
        }
    }

}
