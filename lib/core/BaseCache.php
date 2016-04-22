<?php

namespace litepubl\core;

class BaseCache
{

    abstract public function getString($filename);
    abstract public function setString($filename, $str);

    public function set($filename, $data) {
        $this->setString($filename, $this->serialize($data));
    }

    public function get($filename) {
        if ($s = $this->getString($filename)) {
            return $this->unserialize($s);
        }

        return false;
    }

    public function serialize($data) {
        return serialize($data);
    }

    public function unserialize(&$data) {
        return unserialize($data);
    }

}