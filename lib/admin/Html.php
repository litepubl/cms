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

    public static function getadminlink($path, $params) {
        return litepubl::$site->url . $path . litepubl::$site->q . $params;
    }

    public static function getlink($url, $title) {
        return sprintf('<a href="%s%s">%s</a>', litepubl::$site->url, $url, $title);
    }

    public static function array2combo(array $items, $selected) {
        $result = '';
        foreach ($items as $i => $title) {
            $result.= sprintf('<option value="%s" %s>%s</option>', $i, $i == $selected ? 'selected' : '', static ::specchars($title));
        }
        return $result;
    }

    public static function getcombobox($name, array $items, $selected) {
        return sprintf('<select name="%1$s" id="%1$s">%2$s</select>', $name, static ::array2combo($items, $selected));
    }

    public function adminform($tml, targs $args) {
        return Admin::i()->form($tml, $args);
    }

    public function getupload($name) {
        return $this->getinput('upload', $name, '', '');
    }

    public function getcheckbox($name, $value) {
        return $this->getinput('checkbox', $name, $value ? 'checked="checked"' : '', '$lang.' . $name);
    }

    public function getradioitems($name, array $items, $selected) {
        $result = '';
        $theme = Theme::i();
        foreach ($items as $index => $title) {
            $result.= $theme->getradio($name, $index, static ::specchars($title) , $index == $selected);
        }
        return $result;
    }

    public function getsubmit() {
        $result = '';
        $theme = Theme::i();
        $lang = tlocal::i();

        $a = func_get_args();
        foreach ($a as $name) {
            $result.= strtr($theme->templates['content.admin.button'], array(
                '$lang.$name' => $lang->__get($name) ,
                '$name' => $name,
            ));
        }

        return $result;
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

    public function confirmdelete($id, $adminurl, $mesg) {
        $args = new Args();
        $args->id = $id;
        $args->action = 'delete';
        $args->adminurl = $adminurl;
        $args->confirm = $mesg;

        $admin = Admin::i();
        return $admin->parsearg($admin->templates['confirmform'], $args);
    }

    public function confirm_delete($owner, $adminurl) {
        $id = (int)static ::getparam('id', 0);
        if (!$owner->itemexists($id)) return $this->h4red->notfound;
        if (isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1)) {
            $owner->delete($id);
            return $this->h4->successdeleted;
        } else {
            $args = new Args();
            $args->id = $id;
            $args->adminurl = $adminurl;
            $args->action = 'delete';
            $args->confirm = tlocal::i()->confirmdelete;

            $admin = Admin::i();
            return $this->parsearg($admin->templates['confirmform'], $args);
        }
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