<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tadminmaxcount extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function dogetcontent(twidget $widget, targs $args){
    $args->maxcount = $widget->maxcount;
    return $this->html->parsearg('[text=maxcount]', $args);
  }
  
  protected function doprocessform(twidget $widget)  {
    $widget->maxcount = (int) $_POST['maxcount'];
  }
  
}//class