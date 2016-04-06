<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class getter {
    public $get;
    public $set;

    public function __construct($get = null, $set = null) {
        $this->get = $get;
        $this->set = $set;
    }

    public function __get($name) {
        return call_user_func_array($this->get, array(
            $name
        ));
    }

    public function __set($name, $value) {
        call_user_func_array($this->set, array(
            $name,
            $value
        ));
    }

}