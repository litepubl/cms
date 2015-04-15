<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpolltypes extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    return parent::gethead() . tuitabs::gethead();
  }
  
  public function getcontent() {
    $result = '';
    $types = tpolltypes::i();
    $html = tadminhtml::i();
    $lang = tlocal::admin('polls');
    $args = new targs();
    
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    if (isset($types->items[$type])) {
      $args->type = $type;
      $tabs = new tuitabs();
      foreach ($types->items[$type] as $name => $value) {
        $args->$name = $value;
        $tabs->add($lang->$name, "[editor=$name]");
      }
      $args->formtitle = $lang->edittype;
      $result .= $html->adminform($tabs->get(), $args);
    }
    
    $result .= $html->h4->alltypes;
    $result .= '<ul>';
    $adminurl = $html->getadminlink($this->url, 'type=');
    foreach ($types->items as $type => $item) {
      $result .= sprintf('<li><a href="%s%2$s" title="%2$s">%2$s</a></li>', $adminurl, $type);
    }
    $result .= '</ul>';
    
    return $result;
  }
  
  public function processform() {
    $types = tpolltypes::i();
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    if (isset($types->items[$type])) {
      foreach ($types->items[$type] as $name => $value) {
        if (isset($_POST[$name])) $types->items[$type][$name] = $_POST[$name];
      }
      $types->save();
    }
    
    return '';
  }
  
}//class