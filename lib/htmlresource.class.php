<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
class tadminhtml {
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
  public $section;
  public $searchsect;
  public $ini;
  private $map;
  private $section_stack;

  public static function i() {
    $self = getinstance(__class__);
    if (count($self->ini) == 0) $self->load();
    return $self;
  }

  public static function getinstance($section) {
    $self = self::i();
    $self->section = $section;
    tlocal::i($section);
    return $self;
  }

  public function __construct() {
    $this->ini = array();
    $this->searchsect = array(
      'common'
    );
    tlocal::usefile('admin');
  }

  public function __get($name) {
    if (isset($this->ini[$this->section][$name])) return $this->ini[$this->section][$name];
    foreach ($this->searchsect as $section) {
      if (isset($this->ini[$section][$name])) return $this->ini[$section][$name];
    }

    if (in_array($name, self::$tags)) return new thtmltag($name);
    if (strend($name, 'red') && in_array(substr($name, 0, -3) , self::$tags)) return new redtag($name);

    throw new Exception("the requested $name item not found in $this->section section");
  }

  public function __call($name, $params) {
    if ($name == 'getinput') return call_user_func_array(array(
      ttheme::i() ,
      'getinput'
    ) , $params);
    $s = $this->__get($name);
    if (is_object($s) && ($s instanceof thtmltag)) return sprintf('<%1$s>%2$s</%1$s>', $name, $params[0]);

    if ($name == 'h4error') return sprintf('<h4 class="red">%s</h4>', $params[0]);

    $args = isset($params[0]) && $params[0] instanceof targs ? $params[0] : new targs();
    return $this->parsearg($s, $args);
  }

  public function parsearg($s, targs $args) {
    return admintheme::i()->parsearg($s, $args);
  }

  public function addsearch() {
    $a = func_get_args();
    foreach ($a as $sect) {
      if (!in_array($sect, $this->searchsect)) $this->searchsect[] = $sect;
    }
  }

  public function push_section($section) {
    if (!isset($this->section_stack)) $this->section_stack = array();
    $lang = tlocal::i();
    $this->section_stack[] = array(
      $this->section,
      $lang->section
    );

    $this->section = $section;
    $lang->section = $section;
  }

  public function pop_section() {
    $a = array_pop($this->section_stack);
    $this->section = $a[0];
    tlocal::i()->section = $a[1];
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
    $filename = tlocal::getcachedir() . 'adminhtml';
    if (tfilestorage::loadvar($filename, $v) && is_array($v)) {
      $this->ini = $v + $this->ini;
    } else {
      $merger = tlocalmerger::i();
      $merger->parsehtml();
    }
  }

  public function loadinstall() {
    if (isset($this->ini['installation'])) return;
    tlocal::usefile('install');
    if ($v = parse_ini_file(litepublisher::$paths->languages . 'install.ini', true)) {
      $this->ini = $v + $this->ini;
    }
  }

  public static function getparam($name, $default) {
    return !empty($_GET[$name]) ? $_GET[$name] : (!empty($_POST[$name]) ? $_POST[$name] : $default);
  }

  public static function idparam() {
    return (int)self::getparam('id', 0);
  }

  public static function getadminlink($path, $params) {
    return litepublisher::$site->url . $path . litepublisher::$site->q . $params;
  }

  public static function getlink($url, $title) {
    return sprintf('<a href="%s%s">%s</a>', litepublisher::$site->url, $url, $title);
  }

  public static function array2combo(array $items, $selected) {
    $result = '';
    foreach ($items as $i => $title) {
      $result.= sprintf('<option value="%s" %s>%s</option>', $i, $i == $selected ? 'selected' : '', self::specchars($title));
    }
    return $result;
  }

  public static function getcombobox($name, array $items, $selected) {
    return sprintf('<select name="%1$s" id="%1$s">%2$s</select>', $name, self::array2combo($items, $selected));
  }

  public function adminform($tml, targs $args) {
    $args->items = $this->parsearg($tml, $args);
    return $this->parsearg(ttheme::i()->templates['content.admin.form'], $args);
  }

  public function getupload($name) {
    return $this->getinput('upload', $name, '', '');
  }

  public function getcheckbox($name, $value) {
    return $this->getinput('checkbox', $name, $value ? 'checked="checked"' : '', '$lang.' . $name);
  }

  public function getradioitems($name, array $items, $selected) {
    $result = '';
    $theme = ttheme::i();
    $tml = $theme->templates['content.admin.radioitem'];
    foreach ($items as $index => $value) {
      $result.= strtr($tml, array(
        '$index' => $index,
        '$checked' => $index == $selected ? 'checked="checked"' : '',
        '$name' => $name,
        '$value' => self::specchars($value)
      ));
    }
    return $result;
  }

  public function getsubmit() {
    $result = '';
    $theme = ttheme::i();
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
    if ($date == '0000-00-00 00:00:00') return tlocal::i()->noword;
    return tlocal::date(strtotime($date) , 'd F Y');
  }

  public function gettable($head, $body) {
    return admintheme::i()->gettable($head, $body);
  }

  public function buildtable(array $items, array $tablestruct) {
    $tb = new tablebuilder();
    $tb->setstruct($tablestruct);
    return $tb->build($items);
  }

  public function getitemscount($from, $to, $count) {
    return sprintf($this->h4->itemscount, $from, $to, $count);
  }

  public function confirmdelete($id, $adminurl, $mesg) {
    $args = new targs();
    $args->id = $id;
    $args->action = 'delete';
    $args->adminurl = $adminurl;
    $args->confirm = $mesg;

    $admin = admintheme::i();
    return $admin->parsearg($admin->templates['confirmform'], $args);
  }

  public function confirm_delete($owner, $adminurl) {
    $id = (int)self::getparam('id', 0);
    if (!$owner->itemexists($id)) return $this->h4red->notfound;
    if (isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1)) {
      $owner->delete($id);
      return $this->h4->successdeleted;
    } else {
      $args = new targs();
      $args->id = $id;
      $args->adminurl = $adminurl;
      $args->action = 'delete';
      $args->confirm = tlocal::i()->confirmdelete;

      $admin = admintheme::i();
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

  public function inidir($dir) {
    $filename = $dir . 'html.ini';
    if (!isset(inifiles::$files[$filename])) {
      $html_ini = inifiles::cache($filename);
      if (is_array($html_ini)) {
        $this->ini = $html_ini + $this->ini;
        $keys = array_keys($html_ini);
        $this->section = array_shift($keys);
        $this->searchsect[] = $this->section;
      }
    }

    tlocal::inicache($dir . litepublisher::$options->language . '.admin.ini');
    return $this;
  }

  public function iniplugin($class) {
    return $this->inidir(litepublisher::$classes->getresourcedir($class));
  }

} //class