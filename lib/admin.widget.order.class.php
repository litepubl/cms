<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tadminorderwidget extends tadminwidget {

  public static function i() {
    return getinstance(__class__);
  }

  protected function dogetcontent(twidget $widget, targs $args) {
    $idview = tadminhtml::getparam('idview', 1);
    $view = tview::i($idview);
    $args->sidebar = tadminhtml::array2combo(tadminwidgets::getsidebarnames($view) , $widget->sidebar);
    $args->order = tadminhtml::array2combo(range(-1, 10) , $widget->order + 1);
    $args->ajax = $widget->ajax;
    return $this->html->parsearg('[combo=sidebar] [combo=order] [checkbox=ajax]', $args);
  }

  protected function doprocessform(twidget $widget) {
    $widget->sidebar = (int)$_POST['sidebar'];
    $widget->order = ((int)$_POST['order'] - 1);
    $widget->ajax = isset($_POST['ajax']);
  }

} //class