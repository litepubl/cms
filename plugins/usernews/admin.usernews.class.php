<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminusernews {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tusernews::i();
    $lang = tlocal::admin('usernews');
    $args = new targs();
    $form = '';
    foreach (array('_changeposts', '_canupload', '_candeletefile', 'checkspam', 'insertsource') as $name) {
      $args->$name = $plugin->data[$name];
      //$args->data["\$lang.$name"] = $about[$name];
      $form .= "[checkbox=$name]";
    }
    
    foreach (array('sourcetml', 'editorfile') as $name) {
      $args->$name = $plugin->data[$name];
      //$args->data["\$lang.$name"] = $about[$name . 'label'];
      $form .= "[text=$name]";
    }
    
    $args->formtitle = $lang->formtitle;
    $html = tadminhtml::i();
    return $html->adminform($form, $args);
  }
  
  public function processform() {
    $plugin = tusernews::i();
    foreach (array('_changeposts', '_canupload', '_candeletefile', 'checkspam', 'insertsource') as $name) {
      $plugin->data[$name] = isset($_POST[$name]);
    }
    foreach (array('sourcetml', 'editorfile') as $name) {
      $plugin->data[$name] = $_POST[$name];
    }
    
    $plugin->save();
  }
  
}//class