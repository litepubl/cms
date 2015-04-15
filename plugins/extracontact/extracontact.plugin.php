<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class textracontact extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $contact = tcontactform::singleinstance('tcontactform');
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::i();
    $items = '';
    foreach ($contact->data['extra'] as $name => $title) {
      $items .= "$name =$title\n";
    }
    $args->items = $items;
    
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.items'] = $about['items'];
    $html = tadminhtml::i();
    return $html->adminform('[editor=items]', $args);
  }
  
  public function processform() {
    $contact = tcontactform::singleinstance('tcontactform');
    $contact->data['extra'] = tini2array::parsesection(trim($_POST['items']));
    $contact->save();
  }
  
}//class