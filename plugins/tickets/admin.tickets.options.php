<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminticketoptions extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $lang = tlocal::admin('tickets');
    $args = new targs();
    $args->formtitle = $lang->admincats;
    $tickets = ttickets::i();
    return $this->html->adminform(tposteditor::getcategories($tickets->cats), $args);
  }
  
  public function processform() {
    $tickets = ttickets::i();
    $tickets->cats = tposteditor::processcategories();
    $tickets->save();
  }
  
}//class