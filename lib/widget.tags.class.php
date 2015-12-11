<?php

class ttagswidget extends tcommontagswidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.tags';
    $this->template = 'tags';
    $this->sortname = 'title';
    $this->showcount = false;
  }
  
  public function getdeftitle() {
    return tlocal::get('default', 'tags');
  }
  
  public function getowner() {
    return ttags::i();
  }
  
}//class