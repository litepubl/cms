<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tadminticketoptions extends tadminmenu {

  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  public function getcontent() {
    $lang = tlocal::admin('tickets');
    $args = new targs();
    $args->formtitle = $lang->admincats;
    $tickets = ttickets::i();
    return $this->html->adminform($this->admintheme->getcats($tickets->cats) , $args);
  }

  public function processform() {
    $tickets = ttickets::i();
    $tickets->cats = $this->admintheme->processcategories();
    $tickets->save();
  }

} //class