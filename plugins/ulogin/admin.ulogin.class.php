<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class adminulogin implements iadmin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $ulogin = ulogin ::i();
    $html = tadminhtml::i();
    $args = new targs();
    $lang = tplugins::getnamelang('ulogin');
    $args->formtitle = $lang->options;
    $args->panel = $ulogin->panel;
    $args->button = $ulogin->button;
    
    return $html->adminform('
    [editor=panel]
    [editor=button]
    ', $args);
  }
  
  public function processform() {
    $ulogin = ulogin ::i();
    $ulogin->panel = trim($_POST['panel']);
    $ulogin->button = trim($_POST['button']);
    $ulogin->save();
    
    $alogin = tadminlogin::i();
    $alogin ->widget = $ulogin->addpanel($alogin ->widget, $ulogin->panel);
    $alogin->save();
    
    $areg = tadminreguser::i();
    $areg->widget = $ulogin->addpanel($areg->widget, $ulogin->panel);
    $areg->save();
    
    $tc = ttemplatecomments::i();
    $tc->regaccount = $ulogin->addpanel($tc->regaccount, $ulogin->button);
    $tc->save();
  }
  
}//class