<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincustomtitle {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tcustomtitle::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::i();
    $args->post = $plugin->post;
    $args->tag = $plugin->tag;
    $args->home = $plugin->home;
    $args->archive = $plugin->archive;
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.tag'] = $about['tagcat'];
    
    $html = tadminhtml::i();
    return $html->adminform('[text=post]
    [text=tag]
    [text=home]
    [text=archive]', $args);
  }
  
  public function processform() {
    $plugin = tcustomtitle::i();
    $plugin->post = $_POST['post'];
    $plugin->tag = $_POST['tag'];
    $plugin->home = $_POST['home'];
    $plugin->archive = $_POST['archive'];
    $plugin->save();
    litepublisher::$urlmap->clearcache();
  }
  
}//class