<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class storagejson extends storage {

    public function __construct() {
        $this->ext = '.json';
    }

    public function serialize(array $data) {
        return \json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | (litepubl::$debug ? JSON_PRETTY_PRINT : 0));
    }

    public function unserialize($str) {
        return \json_decode($s, true);
    }

    public function before($str) {
        return $str;
    }

    public function after($str) {
        return $str;
    }

} //class