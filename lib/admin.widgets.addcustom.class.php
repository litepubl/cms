<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class addcustomwidget extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $widget = tcustomwidget::i();
    return $widget->admin->getcontent();
  }
  
  public function processform() {
    $widget = tcustomwidget::i();
    return $widget->admin->processform();
  }
  
}//class