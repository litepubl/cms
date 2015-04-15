<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsameposts extends tadminorderwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $widgets = twidgets::i();
    $this->widget = $widgets->getwidget($widgets->find(tsameposts::i()));
  }
  
  protected function dogetcontent(twidget $widget, targs $args){
    $args->maxcount = $widget->maxcount;
    $result = $this->html->parsearg('[text=maxcount]', $args);
    $result .= parent::dogetcontent($widget, $args);
    return $result;
  }
  
  protected function doprocessform(twidget $widget)  {
    $widget->maxcount = (int) $_POST['maxcount'];
    $widget->postschanged();
    return parent::doprocessform($widget);
  }
  
}//class