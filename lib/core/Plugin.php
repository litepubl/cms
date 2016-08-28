<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\core;

class Plugin extends Events
{

    protected function create()
    {
        parent::create();
        $this->basename = 'plugins/' . strtolower(str_replace('\\', '-', get_class($this)));
    }

    public function addClass($classname, $filename)
    {
        $ns = dirname(get_class($this));
        $reflector = new \ReflectionClass($class);
        $dir = dirname($reflector->getFileName());

        $this->getApp()->classes->add($ns . '\\' . $classname, $dir . '/' . $filename);
    }
}
