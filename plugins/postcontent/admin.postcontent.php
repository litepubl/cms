<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpostcontentplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tpostcontentplugin ::i();
    $html = tadminhtml::i();
    $args = targs::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.before'] = $about['before'];
    $args->data['$lang.after'] = $about['after'];
    $args->before = $plugin->before;
    $args->after = $plugin->after;
    return $html->adminform('[editor=before] [editor=after]', $args);
  }
  
  public function processform() {
    extract($_POST, EXTR_SKIP);
    $plugin = tpostcontentplugin ::i();
    $plugin->lock();
    $plugin->before = $before;
    $plugin->after = $after;
    $plugin->unlock();
    return '';
  }
  
}