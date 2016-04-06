<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class titem extends tdata {
    public static $instances;

    public static function i($id = 0) {
        return static ::iteminstance(get_called_class() , (int)$id);
    }

    public static function iteminstance($class, $id = 0) {
        //fix namespace
        if (!strpos($class, '\\') && !class_exists($class)) {
            $class = 'litepubl\\' . $class;
        }

        $name = call_user_func_array(array(
            $class,
            'getinstancename'
        ) , array());

        if (!isset(static ::$instances)) {
            static ::$instances = array();
        }

        if (isset(static ::$instances[$name][$id])) {
            return static ::$instances[$name][$id];
        }

        $self = litepubl::$classes->newitem($name, $class, $id);
        return $self->loaddata($id);
    }

    public function loaddata($id) {
        $this->data['id'] = $id;
        if ($id != 0) {
            if (!$this->load()) {
                $this->free();
                return false;
            }
            static ::$instances[$this->instancename][$id] = $this;
        }
        return $this;
    }

    public function free() {
        unset(static ::$instances[$this->getinstancename() ][$this->id]);
    }

    public function __construct() {
        parent::__construct();
        $this->data['id'] = 0;
    }

    public function __destruct() {
        $this->free();
    }

    public function __set($name, $value) {
        if (parent::__set($name, $value)) return true;
        return $this->Error("Field $name not exists in class " . get_class($this));
    }

    public function setid($id) {
        if ($id != $this->id) {
            $name = $this->instancename;
            if (!isset(static ::$instances)) static ::$instances = array();
            if (!isset(static ::$instances[$name])) static ::$instances[$name] = array();
            $a = & static ::$instances[$this->instancename];
            if (isset($a[$this->id])) unset($a[$this->id]);
            if (isset($a[$id])) $a[$id] = 0;
            $a[$id] = $this;
            $this->data['id'] = $id;
        }
    }

    public function request($id) {
        if ($id != $this->id) {
            $this->setid($id);
            if (!$this->load()) return 404;
        }
    }

    public static function deletedir($dir) {
        if (!@file_exists($dir)) return false;
        tfiler::delete($dir, true, true);
        @rmdir($dir);
    }

}