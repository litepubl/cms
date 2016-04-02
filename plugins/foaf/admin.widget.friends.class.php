<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tadminfriendswidget extends tadminwidget {

  public static function i() {
    return getinstance(__class__);
  }

  protected function dogetcontent(twidget $widget, targs $args) {
    $args->maxcount = $widget->maxcount;
    $args->redir = $widget->redir;
    return tadminhtml::i()->parsearg('[checkbox=redir] [text=maxcount]', $args);
  }

  protected function doprocessform(twidget $widget) {
    $widget->maxcount = (int)$_POST['maxcount'];
    $widget->redir = isset($_POST['redir']);
  }

} //class