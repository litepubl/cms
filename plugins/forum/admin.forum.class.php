<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminforum implements iadmin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $forum = tforum::i();
    $html = tadminhtml::i();
    $args = new targs();
    
    $html->section = 'editor';
    $lang = tlocal::i('editor');
    
    $args->comstatus= tadminhtml::array2combo(array(
    'closed' => $lang->closed,
    'reg' => $lang->reg,
    'guest' => $lang->guest,
    'comuser' => $lang->comuser
    ), $forum->comstatus);
    
    $lang = tlocal::admin('forum');
    $args->rootcat = tposteditor::getcombocategories(array(), $forum->rootcat);
    $args->moderate = $forum->moderate;
    
    $args->formtitle = $lang->options;
    return $html->adminform('
    [combo=rootcat]
    [combo=comstatus]
    [checkbox=moderate]
    ' .
    tadminviews::getcomboview($forum->idview) .
    tadminperms::getcombo(0)
    , $args);
  }
  
  public function processform() {
    extract($_POST, EXTR_SKIP);
    $forum = tforum::i();
    $forum->rootcat = (int) $rootcat;
    $forum->idview = (int) $idview;
    $forum->idperm = (int) $idperm;
    $forum->moderate = isset($moderate);
    $forum->save();
  }
  
}//class