<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tadminmetawidget extends tadminwidget {

  public static function i() {
    return getinstance(__class__);
  }

  protected function dogetcontent(twidget $widget, targs $args) {
    $html = $this->html;
    $result = '';
    foreach ($widget->items as $name => $item) {
      $result.= $html->getinput('checkbox', $name, $item['enabled'] ? 'checked="checked"' : '', $item['title']);
    }
    return $result;
  }

  protected function doprocessform(twidget $widget) {
    foreach ($widget->items as $name => $item) {
      $widget->items[$name]['enabled'] = isset($_POST[$name]);
    }
  }

} //class