<?php

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
  
}//class