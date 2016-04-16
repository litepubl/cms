<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Admin;
use litepubl\view\Args;

class Html
{
    public static $tags = array(
        'h1',
        'h2',
        'h3',
        'h4',
        'p',
        'li',
        'ul',
        'strong',
        'div',
        'span'
    );

    public static function i() {
        return getinstance(get_called_class());
    }

    public static function getinstance($section) {
        tlocal::i($section);
        return static ::i();
    }

    public function __construct() {
        tlocal::usefile('admin');
    }

    public function __get($name) {
        if (in_array($name, static ::$tags)) {
return new Tag($name);
}
        if (strend($name, 'red') && in_array(substr($name, 0, -3) , static ::$tags)) {
return new redtag($name);
}

        throw new \Exception("the requested $name item not found");
    }

    public function __call($name, $params) {
        if ($name == 'getinput') return call_user_func_array(array(
            Theme::i() ,
            'getinput'
        ) , $params);

        $s = $this->__get($name);
        if (is_object($s) && ($s instanceof thtmltag)) return sprintf('<%1$s>%2$s</%1$s>', $name, $params[0]);

        if ($name == 'h4error') return sprintf('<h4 class="red">%s</h4>', $params[0]);

        $args = isset($params[0]) && $params[0] instanceof targs ? $params[0] : new Args();
        return $this->parsearg($s, $args);
    }

    public function parsearg($s, targs $args) {
        return Admin::i()->parsearg($s, $args);
    }

    public static function specchars($s) {
        return strtr(htmlspecialchars($s) , array(
            '"' => '&quot;',
            "'" => '&#39;',
            '$' => '&#36;',
            '%' => '&#37;',
            '_' => '&#95;'
        ));
    }

    public function fixquote($s) {
        $s = str_replace("\\'", '\"', $s);
        $s = str_replace("'", '"', $s);
        return str_replace('\"', "'", $s);
    }

    public function load() {
    }

    public function loadinstall() {
        if (isset($this->ini['installation'])) return;
        tlocal::usefile('install');
        if ($v = parse_ini_file(litepubl::$paths->languages . 'install.ini', true)) {
            $this->ini = $v + $this->ini;
        }
    }

    public function adminform($tml, targs $args) {
        return Admin::i()->form($tml, $args);
    }


    public function getedit($name, $value, $title) {
        return $this->getinput('text', $name, $value, $title);
    }

    public function getcombo($name, $value, $title) {
        return $this->getinput('combo', $name, $value, $title);
    }

    public static function datestr($date) {
        if ($date == '0000-00-00 00:00:00') {
return tlocal::i()->noword;
}

        return tlocal::date(strtotime($date) , 'd F Y');
    }

    public function gettable($head, $body) {
        return Admin::i()->gettable($head, $body);
    }

    public function buildtable(array $items, array $tablestruct) {
        $tb = new Table();
        $tb->setstruct($tablestruct);
        return $tb->build($items);
    }

    public function getitemscount($from, $to, $count) {
        return sprintf($this->h4->itemscount, $from, $to, $count);
    }

    public static function check2array($prefix) {
        $result = array();
        foreach ($_POST as $key => $value) {
            if (strbegin($key, $prefix)) {
                $result[] = is_numeric($value) ? (int)$value : $value;
            }
        }
        return $result;
    }

}