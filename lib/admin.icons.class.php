<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminicons extends tadminmenu {
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getradio($idicon) {
    $items = self::getallicons();
    if (count($items) == 0) return '';
    $html = tadminhtml::i();
    $html->section = 'files';
    $args = targs::i();
    //add empty icon
    $args->id = 0;
    $args->checked = 0 == $idicon;
    $args->filename = '';
    $args->title = tlocal::i()->empty;
    $result = $html->radioicon($args);
    $files = tfiles::i();
    $url = litepublisher::$site->files . '/files/';
    foreach ($items as $id) {
      $item = $files->getitem($id);
      $args->add($item);
      $args->id = $id;
      $args->checked = $id == $idicon;
      $args->link = $url . $item['filename'];
      $result .= $html->radioicon($args);
    }
    
    return $result;
  }
  
  public static function getallicons() {
    $files = tfiles::i();
    if ($files->dbversion) {
      if ($result = $files->select("parent = 0 and media = 'icon'", "")) return $result;
      return array();
    } else {
      $result = array();
      foreach ($files->items as $id => $item) {
        if ('icon' == $item['media']) $result[] = $id;
      }
      return $result;
    }
  }
  
  public function getcontent() {
    $result = '';
    $files = tfiles::i();
    $icons = ticons::i();
    $html = $this->html;
    $lang = tlocal::admin('files');
    $args = targs::i();
    $a = array();
    //добавить 0 для отсутствия иконки
    $a[0] = $lang->noicon;
    
    $allicons = self::getallicons();
    foreach ($allicons as $id) {
      $args->id = $id;
      $item = $files->getitem($id);
      $args->add($item);
      $a[$id] = $html->comboitem($args);
    }
    
    $list = '';
    foreach ($icons->items as $name => $id) {
      $args->name = $name;
      $title = $lang->$name;
      if ($title == '') $title = tlocal::usefile('install')->$name;
      $args->title = $title;
      $args->combo = $html->array2combo($a, $id);
      $list .= $html->iconitem($args);
    }
    
    $args->formtitle = $lang->iconheader;
    $result .= $html->adminform($list, $args);
    return $html->fixquote($result);
  }
  
  public function processform() {
    $icons = ticons::i();
    foreach ($_POST as $name => $value) {
      if (isset($icons->items[$name])) $icons->items[$name] = (int) $value;
    }
    $icons->save();
    
    $lang = tlocal::i('files');
    return $this->html->h2->iconupdated;
  }
  
}//class
?>