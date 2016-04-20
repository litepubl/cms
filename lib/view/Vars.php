<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\view;

class Vars
 {
    public $keys = array();

    public function __destruct() {
        foreach ($this->keys as $name) {
            if (isset(BaseTheme::$vars[$name])) {
                unset(BaseTheme::$vars[$name]);
            }
        }
    }

    public function __get($name) {
        return BaseTheme::$vars[$name];
    }

    public function __set($name, $value) {
        BaseTheme::$vars[$name] = $value;

        if (!in_array($name, $this->keys)) {
            $this->keys[] = $name;
        }
    }

    public function __isset($name) {
        return isset(BaseTheme::$vars[$name]);
    }

    public function __unset($name) {
        unset(BaseTheme::$vars[$name]);
    }

}