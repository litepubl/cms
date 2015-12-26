<?php

class tadmintagswidget extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function dogetcontent(twidget $widget, targs $args){
    $args->showcount = $widget->showcount;
    $args->showsubitems = $widget->showsubitems;
    $args->maxcount = $widget->maxcount;
    $args->sort = tadminhtml::array2combo(tlocal::i()->ini['sortnametags'], $widget->sortname);
    return $this->html->parsearg('[combo=sort] [checkbox=showsubitems] [checkbox=showcount] [text=maxcount]', $args);
  }
  
  protected function doprocessform(twidget $widget)  {
    extract($_POST, EXTR_SKIP);
    $widget->maxcount = (int) $maxcount;
    $widget->showcount = isset($showcount);
    $widget->showsubitems = isset($showsubitems);
    $widget->sortname = $sort;
  }
  
}//class
