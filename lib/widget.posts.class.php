<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tpostswidget extends twidget {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->basename = 'widget.posts';
    $this->template = 'posts';
    $this->adminclass = 'tadminmaxcount';
    $this->data['maxcount'] = 10;
  }

  public function getdeftitle() {
    return tlocal::get('default', 'recentposts');
  }

  public function getcontent($id, $sidebar) {
    $posts = tposts::i();
    $list = $posts->getpage(0, 1, $this->maxcount, false);
    $theme = ttheme::i();
    return $theme->getpostswidgetcontent($list, $sidebar, '');
  }

} //class