<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tplugin extends tevents {

    protected function create() {
        parent::create();
        $this->basename = 'plugins/' . strtolower(str_replace('\\', '-', get_class($this)));
    }

    public function addClass($classname, $filename) {
        $ns = dirname(get_class($this));
        $reflector = new \ReflectionClass($class);
        $dir = dirname($reflector->getFileName());

        litepubl::$classes->add($ns . '\\' . $classname, $dir . '/' . $filename);
    }

}