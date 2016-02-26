<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
class tcategorieswidget extends tcommontagswidget {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->basename = 'widget.categories';
    $this->template = 'categories';
  }

  public function getdeftitle() {
    return tlocal::get('default', 'categories');
  }

  public function getowner() {
    return tcategories::i();
  }

} //class