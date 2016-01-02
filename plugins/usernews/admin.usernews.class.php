<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
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