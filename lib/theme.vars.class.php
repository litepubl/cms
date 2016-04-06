<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class themevars {
    public $keys = array();

    public function __destruct() {
        foreach ($this->keys as $name) {
            if (isset(basetheme::$vars[$name])) {
                unset(basetheme::$vars[$name]);
            }
        }
    }

    public function __get($name) {
        return basetheme::$vars[$name];
    }

    public function __set($name, $value) {
        basetheme::$vars[$name] = $value;

        if (!in_array($name, $this->keys)) {
            $this->keys[] = $name;
        }
    }

    public function __isset($name) {
        return isset(basetheme::$vars[$name]);
    }

    public function __unset($name) {
        unset(basetheme::$vars[$name]);
    }

}