<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class adminlikebuttons {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = new targs();
    $args->formtitle = $about['name'];
    $args->facebookapp = likebuttons::i()->facebook_appid;
    $args->data['$lang.facebookapp'] = $about['facebookapp'];
    
    $html = tadminhtml::i();
    return $html->adminform('[text=facebookapp]', $args);
  }
  
  public function processform() {
    likebuttons::i()->facebook_appid = $_POST['facebookapp'];
  }
  
}//class