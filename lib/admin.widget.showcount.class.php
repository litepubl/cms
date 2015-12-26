<?php

class tadminshowcount extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function dogetcontent(twidget $widget, targs $args){
    $args->showcount = $widget->showcount;
    return $this->html->parsearg('[checkbox=showcount]', $args);
  }
  
  protected function doprocessform(twidget $widget)  {
    $widget->showcount = isset($_POST['showcount']);
  }
  
}//class
