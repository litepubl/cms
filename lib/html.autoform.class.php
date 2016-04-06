<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tautoform {
    const editor = 'editor';
    const text = 'text';
    const checkbox = 'checkbox';
    const hidden = 'hidden';

    public $obj;
    public $props;
    public $section;
    public $_title;

    public static function i() {
        return getinstance(__class__);
    }

    public function __construct(tdata $obj, $section, $titleindex) {
        $this->obj = $obj;
        $this->section = $section;
        $this->props = array();
        $lang = tlocal::i($section);
        $this->_title = $lang->$titleindex;
    }

    public function __set($name, $value) {
        $this->props[] = array(
            'obj' => $this->obj,
            'propname' => $name,
            'type' => $value
        );
    }

    public function __get($name) {
        if (isset($this->obj->$name)) {
            return array(
                'obj' => $this->obj,
                'propname' => $name
            );
        }
        //tlogsubsystem::error(sprintf('The property %s not found in class %s', $name, get_class($this->obj));
        
    }

    public function __call($name, $args) {
        if (isset($this->obj->$name)) {
            $result = array(
                'obj' => $this->obj,
                'propname' => $name,
                'type' => $args[0]
            );
            if (($result['type'] == 'combo') && isset($args[1])) $result['items'] = $args[1];
            return $result;
        }
    }

    public function add() {
        $a = func_get_args();
        foreach ($a as $prop) {
            $this->addprop($prop);
        }
    }

    public function addsingle($obj, $propname, $type) {
        return $this->addprop(array(
            'obj' => $obj,
            'propname' => $propname,
            'type' => $type
        ));
    }

    public function addeditor($obj, $propname) {
        return $this->addsingle($obj, $propname, 'editor');
    }

    public function addprop(array $prop) {
        if (isset($prop['type'])) {
            $type = $prop['type'];
        } else {
            $type = 'text';
            $value = $prop['obj']->{$prop['propname']};
            if (is_bool($value)) {
                $type = 'checkbox';
            } elseif (strpos($value, "\n")) {
                $type = 'editor';
            }
        }

        $item = array(
            'obj' => $prop['obj'],
            'propname' => $prop['propname'],
            'type' => $type,
            'title' => isset($prop['title']) ? $prop['title'] : ''
        );
        if (($type == 'combo') && isset($prop['items'])) $item['items'] = $prop['items'];
        $this->props[] = $item;
        return count($this->props) - 1;
    }

    public function getcontent() {
        $result = '';
        $lang = tlocal::i();
        $theme = ttheme::i();

        foreach ($this->props as $prop) {
            $value = $prop['obj']->{$prop['propname']};
            switch ($prop['type']) {
                case 'text':
                case 'editor':
                    $value = tadminhtml::specchars($value);
                    break;


                case 'checkbox':
                    $value = $value ? 'checked="checked"' : '';
                    break;


                case 'combo':
                    $value = tadminhtml::array2combo($prop['items'], $value);
                    break;
            }

            $result.= strtr($theme->templates['content.admin.' . $prop['type']], array(
                '$lang.$name' => empty($prop['title']) ? $lang->{$prop['propname']} : $prop['title'],
                '$name' => $prop['propname'],
                '$value' => $value
            ));
        }
        return $result;
    }

    public function getform() {
        $args = new targs();
        $args->formtitle = $this->_title;
        $args->items = $this->getcontent();
        $theme = ttheme::i();
        $tml = str_replace('[submit=update]', str_replace('$name', 'update', $theme->templates['content.admin.submit']) , $theme->templates['content.admin.form']);
        return $theme->parsearg($tml, $args);
    }

    public function processform() {
        foreach ($this->props as $prop) {
            if (method_exists($prop['obj'], 'lock')) $prop['obj']->lock();
        }

        foreach ($this->props as $prop) {
            $name = $prop['propname'];
            if (isset($_POST[$name])) {
                $value = trim($_POST[$name]);
                if ($prop['type'] == 'checkbox') $value = true;
            } else {
                $value = false;
            }
            $prop['obj']->$name = $value;
        }

        foreach ($this->props as $prop) {
            if (method_exists($prop['obj'], 'unlock')) $prop['obj']->unlock();
        }
    }

} //class