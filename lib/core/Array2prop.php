<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

class Array2prop
 {
    public $array;

    public function __construct(array $a = null) {
        $this->array = $a;
    }

    public function __get($name) {
        return $this->array[$name];
    }

    public function __set($name, $value) {
        $this->array[$name] = $value;
    }

    public function __isset($name) {
        return array_key_exists($name, $this->array);
    }

    public function __tostring() {
        return $this->array[''];
    }

}