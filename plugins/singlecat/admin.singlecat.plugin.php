<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tadminsinglecat {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tsinglecat::i();
    $html= tadminhtml::i();
    $lang = tplugins::getlangabout(__file__);
    $args = targs::i();
    $args->maxcount = $plugin->maxcount;
    $args->invertorder = $plugin->invertorder;
    $args->tml = $plugin->tml;
    $args->tmlitems = $plugin->tmlitems;
    $args->formtitle = $lang->formtitle;
    return $html->adminform(
    ' [checkbox=invertorder]
    [text=maxcount]
    [editor=tml]
    [editor=tmlitems]',
    $args);
  }
  
  public function processform()  {
    $plugin = tsinglecat::i();
    $plugin->invertorder = isset($_POST['invertorder']);
    $plugin->maxcount = (int) $_POST['maxcount'];
    $plugin->tml = $_POST['tml'];
    $plugin->tmlitems = $_POST['tmlitems'];
    $plugin->save();
  }
  
}//class