<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;
use litepubl\view\Filter;
use litepubl\view\Schema;

class tmenu extends \litepubl\core\Item implements \litepubl\theme\ViewInterface
{
    public $formresult;
    public static $ownerprops = array(
        'title',
        'url',
        'idurl',
        'parent',
        'order',
        'status'
    );

    public static function i($id = 0) {
        $class = $id == 0 ? get_called_class() : static ::getowner()->items[$id]['class'];
        return static ::iteminstance(get_called_class() , $id);
    }

    public static function iteminstance($class, $id = 0) {
        $single = getinstance($class);
        if ($single->id == $id) {
            return $single;
        }

        if (($id == 0) && ($single->id > 0)) {
            return $single;
        }

        if (($single->id == 0) && ($id > 0)) {
            return $single->loaddata($id);
        }

        return parent::iteminstance($class, $id);
    }

    public static function singleinstance($class) {
        $single = getinstance($class);
        if ($id = $single->get_owner()->class2id($class)) {
            if ($single->id == $id) return $single;
            if (($single->id == 0) && ($id > 0)) return $single->loaddata($id);
        }
        return $single;
    }

    public static function getinstancename() {
        return 'menu';
    }

    public static function getowner() {
        return Menus::i();
    }

    public function get_owner() {
        return static ::getowner();
    }

    protected function create() {
        parent::create();
        $this->formresult = '';
        $this->data = array(
            'id' => 0,
            'author' => 0, //not supported
            'content' => '',
            'rawcontent' => '',
            'keywords' => '',
            'description' => '',
            'head' => '',
            'password' => '',
            'idview' => 1,
            //owner props
            'title' => '',
            'url' => '',
            'idurl' => 0,
            'parent' => 0,
            'order' => 0,
            'status' => 'published'
        );
    }

    public function getbasename() {
        return 'menus' . DIRECTORY_SEPARATOR . $this->id;
    }

    public function __get($name) {
        if ($name == 'content') return $this->formresult . $this->getcontent();
        if ($name == 'id') return $this->data['id'];
        if (method_exists($this, $get = 'get' . $name)) return $this->$get();

        if ($this->is_owner_prop($name)) return $this->getownerprop($name);
        return parent::__get($name);
    }

    public function get_owner_props() {
        return static ::$ownerprops;
    }

    public function is_owner_prop($name) {
        return in_array($name, $this->get_owner_props());
    }

    public function getownerprop($name) {
        $id = $this->data['id'];
        if ($id == 0) {
            return $this->data[$name];
        } else if (isset($this->getowner()->items[$id])) {
            return $this->getowner()->items[$id][$name];
        } else {
            $this->error(sprintf('%s property not found in %d items', $id, $name));
        }
    }

    public function __set($name, $value) {
        if ($this->is_owner_prop($name)) {
            if ($this->id == 0) {
                $this->data[$name] = $value;
            } else {
                $this->owner->setvalue($this->id, $name, $value);
            }
            return;
        }
        parent::__set($name, $value);
    }

    public function __isset($name) {
        if ($this->is_owner_prop($name)) return true;
        return parent::__isset($name);
    }

    public function getschema() {
        return Schema::getSchema($this);
    }

    public function gettheme() {
        return $this->schema->theme;
    }

    public function getadmintheme() {
        return $this->schema->admintheme;
    }

    //ViewInterface
    public function request($id) {
        parent::request($id);
        if ($this->status == 'draft') return 404;
        $this->doprocessform();
    }

    protected function doprocessform() {
        if (isset($_POST) && count($_POST)) {
            $this->formresult.= $this->processform();
        }
    }

    public function processform() {
        return $this->owner->onprocessform($this->id);
    }

    public function gethead() {
        return $this->data['head'];
    }

    public function gettitle() {
        return $this->getownerprop('title');
    }

    public function getkeywords() {
        return $this->data['keywords'];
    }

    public function getdescription() {
        return $this->data['description'];
    }

    public function getIdSchema() {
        return $this->data['idview'];
    }

    public function setIdSchema($id) {
        if ($id != $this->idview) {
            $this->data['idview'] = $id;
            $this->save();
        }
    }

    public function getcont() {
        return $this->theme->parsevar('menu', $this, $this->theme->templates['content.menu']);
    }

    public function getlink() {
        return litepubl::$site->url . $this->url;
    }

    public function getcontent() {
        $result = $this->data['content'];
        $this->owner->callevent('oncontent', array(
            $this, &$result
        ));
        return $result;
    }

    public function setcontent($s) {
        if (!is_string($s)) {
            $this->error('Error! Page content must be string');
        }

        if ($s != $this->rawcontent) {
            $this->rawcontent = $s;
            $filter = Filter::i();
            $this->data['content'] = $filter->filter($s);
        }
    }

} //class
