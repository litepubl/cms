<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;

class MemvarMemcache extends CacheMemcache
{
    public $data;

    public function __construct() {
parent::__construct();
        $this->data = array();
    }

    public function getRevision() {
//nothing, just to override parent method
}

    public function getPrefix() {
        return $this->prefix;
    }

    public function __get($name) {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

            if ($result = $this->get($name)) {
                $this->data[$name] = $result;
            }

        return $result;
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
            $this->set($name, $value);
        }

    public function __unset($name) {
            unset($this->data[$name]);
$this->delete($name);
    }

}