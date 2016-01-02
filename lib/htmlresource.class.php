<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tadminhtml {
  public static $tags = array('h1', 'h2', 'h3', 'h4', 'p', 'li', 'ul', 'strong', 'div', 'span');
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
    $this->searchsect = array('common');
    tlocal::usefile('admin');
  }
  
  public function __get($name) {
    if (isset($this->ini[$this->section][$name])) return $this->ini[$this->section][$name];
    foreach ($this->searchsect as $section) {
      if (isset($this->ini[$section][$name])) return $this->ini[$section][$name];
    }
    
    if (in_array($name, self::$tags)) return new thtmltag($name);
    if (strend($name, 'red') && in_array(substr($name, 0, -3), self::$tags)) return new redtag($name);
    
    throw new Exception("the requested $name item not found in $this->section section");
  }
  
  public function __call($name, $params) {
    if ($name == 'getinput') return call_user_func_array(array(ttheme::i(), 'getinput'), $params);
    $s = $this->__get($name);
    if (is_object($s) && ($s instanceof thtmltag))  return sprintf('<%1$s>%2$s</%1$s>', $name, $params[0]);
    
    if ($name == 'h4error') return sprintf('<h4 class="red">%s</h4>', $params[0]);
    
    $args = isset($params[0]) && $params[0] instanceof targs ? $params[0] : new targs();
    return $this->parsearg($s, $args);
  }
  
  public function parsearg($s, targs $args) {
    if (!is_string($s)) $s = (string) $s;
    $theme = ttheme::i();
    
    // parse tags [form] .. [/form]
    if (is_int($i = strpos($s, '[form]'))) {
      $form = $theme->templates['content.admin.form'];
      $replace = substr($form, 0, strpos($form, '$items'));
      $s = substr_replace($s, $replace, $i, strlen('[form]'));
    }
    
    if ($i = strpos($s, '[/form]')) {
      $replace = substr($form, strrpos($form, '$items') + strlen('$items'));
      $s = substr_replace($s, $replace, $i, strlen('[/form]'));
    }
    
    if (preg_match_all('/\[(editor|checkbox|text|password|combo|hidden|submit|button|calendar|upload)(:|=)(\w*+)\]/i', $s, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $type = $item[1];
        $name = $item[3];
        $varname = '$' . $name;
        //convert spec charsfor editor
        if (!in_array($type, array('checkbox', 'combo', 'calendar', 'upload'))) {
          if (isset($args->data[$varname])) {
            $args->data[$varname] = self::specchars($args->data[$varname]);
          } else {
            $args->data[$varname] = '';
          }
        }
        
        if ($type == 'calendar') {
          $tag = admintheme::i()->getcalendar($name, $args->data[$varname]);
        } else {
          $tag = strtr($theme->templates["content.admin.$type"], array(
          '$name' => $name,
          '$value' => $varname
          ));
        }
        
        $s = str_replace($item[0], $tag, $s);
      }
    }
    
    $s = strtr($s, $args->data);
    return $theme->parse($s);
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
    return strtr(            htmlspecialchars($s), array(
    '"' => '&quot;',
    "'" =>'&#39;',
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
    if( $v = parse_ini_file(litepublisher::$paths->languages . 'install.ini', true)) {
      $this->ini = $v + $this->ini;
    }
  }
  
  public static function getparam($name, $default) {
    return !empty($_GET[$name]) ? $_GET[$name] : (!empty($_POST[$name]) ? $_POST[$name] : $default);
  }
  
  public static function idparam() {
    return (int) self::getparam('id', 0);
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
      $result .= sprintf('<option value="%s" %s>%s</option>', $i, $i == $selected ? 'selected' : '', self::specchars($title));
    }
    return $result;
  }
  
  public static function getcombobox($name, array $items, $selected) {
    return sprintf('<select name="%1$s" id="%1$s">%2$s</select>', $name,
    self::array2combo($items, $selected));
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
      $result .= strtr($tml, array(
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
      $result .= strtr($theme->templates['content.admin.button'], array(
      '$lang.$name' => $lang->__get($name),
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
    return tlocal::date(strtotime($date),'d F Y');
  }
  
  public function gettable($head, $body) {
    return admintheme::i()->gettable($head, $body);
  }
  
  public function tablestruct(array $tablestruct, $args = false) {
    $head = '';
    $body = '<tr>';
    
    foreach ($tablestruct as $index => $item) {
      if (!$item || !count($item)) continue;
      
      if (count($item) == 2) {
        array_unshift($item, 'left');
      }
      
      $colclass = tablebuilder::getcolclass($item[0]);
      $head .= sprintf('<th class="%s">%s</th>', $colclass, $item[1]);
      
      if (is_string($item[2])) {
        $body .= sprintf('<td class="%s">%s</td>', $colclass, $item[2]);
      } else if ($args) {
        $callback_name = 'callback' . $index;
      $args->{$callback_name} = $item[2];
        $body .= sprintf('<td class="%s">$%s</td>', $colclass, $callback_name);
      } else {
        // special case for callback. Add new prop to template vars
        $tableprop =         ttheme::$vars['tableprop'] = tableprop::i();
        $body .= sprintf('<td class="%s">$tableprop.%s</td>', $colclass, $tableprop->addprop($item[2]));
      }
    }
    
    $body .= '</tr>';
    
    return array($head, $body, $args);
  }
  
  public function buildtable(array $items, array $tablestruct) {
    $tb = new tablebuilder();
    $tb->setstruct($tablestruct);
    return $db->build($items);
  }
  
  public function getitemscount($from, $to, $count) {
    return sprintf($this->h4->itemscount, $from, $to, $count);
  }
  
  public function tablevalues(array $a) {
    $body = '';
    foreach ($a as $k => $v) {
      if (is_array($v)) {
        foreach ($v as $kv => $vv) {
          $body .= sprintf('<tr><td>%s</td><td>%s</td></tr>', $kv, $vv);
        }
      } else {
        $body .= sprintf('<tr><td>%s</td><td>%s</td></tr>', $k, $v);
      }
    }
    
    $lang = tlocal::i();
    return $this->gettable("<th>$lang->name</th> <th>$lang->value</th>", $body);
  }
  
  public function singlerow(array $a) {
    $head = '';
    $body = '<tr>';
    foreach ($a as $k => $v) {
      $head .= sprintf('<th>%s</th>', $k);
      $body .= sprintf('<td>%s</td>', $v);
    }
    $body .= '</tr>';
    
    return $this->gettable($head, $body);
  }
  
  public function proplist($tml, array $props) {
    $result = '';
    if (!$tml) $tml = '<li>%s: %s</li>';
    // exclude props with int keys
    $tml_int = '<li>%s</li>';
    
    foreach ($props as $prop => $value) {
      if ($value === false) continue;
      if (is_array($value)) {
        $value = $this->proplist($tml, $value);
      }
      
      if (is_int($prop)) {
        $result .= sprintf($tml_int, $value);
      } else {
        $result .= sprintf($tml, $prop, $value);
      }
    }
    
    return $result ? sprintf('<ul>%s</ul>', $result) : '';
  }
  
  public function linkproplist(array $props) {
    return $this->proplist('<li><a href="' . litepublisher::$site->url . '%s">%s</a></li>', $props);
  }
  
  public function confirmdelete($id, $adminurl, $mesg) {
    $args = new targs();
    $args->id = $id;
    $args->action = 'delete';
    $args->adminurl = $adminurl;
    $args->confirm = $mesg;
    
    $admin = admintheme::i();
    return $this->parsearg($admin->templates['confirmform'], $args);
  }
  
  public function confirm_delete($owner, $adminurl) {
    $id = (int) self::getparam('id', 0);
    if (!$owner->itemexists($id)) return $this->h4red->notfound;
    if  (isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1)) {
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
        $result[] = is_numeric($value) ? (int) $value : $value;
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
  
}//class