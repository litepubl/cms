<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class admintmlstorage implements iadmin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tmlstorage::i();
    $args = new targs();
    $args->formtitle = 'Template storage';
    $html = tadminhtml::i();
    $tabs = new tuitabs();
    foreach ($plugin->items as $classname) {
      if (is_array($obj->data['tml'])) {
        $tab = new tuitabs();
        $obj = getinstance($classname);
        foreach ($obj->data['tml'] as $key => $value) {
          $tab->add($key, $html->getinput('editor',
          $classname . '_text_' . $key, tadminhtml::specchars($value), $key));
        }
        $tabs->add($classname, $tab->get());
      } else {
        $tabs->add($classname, $html->getinput('editor',
        $classname . '_text', tadminhtml::specchars($obj->data['tml']), $classname ));
      }
    }
    
    return tuitabs::gethead() .
    $html->adminform($tabs->get(), $args);
  }
  
  public function processform() {
    $plugin = tmlstorage::i();
    foreach ($plugin->items as $classname => $items) {
      $obj = getinstance($classname);
      if (is_array($obj->data['tml'])) {
        foreach ($obj->data['tml'] as $key => $value) {
          $obj->data['tml'][$key] = $_POST[$classname . '_text_' . $key];
        }
      } else {
        $obj->data['tml'] = $_POST[$classname . '_text'];
      }
      $obj->save();
    }
    
  }
  
}//class