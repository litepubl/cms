<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsimplecontent  extends tevents_itemplate implements itemplate {
  public $text;
  public $html;
  
  public static function i() {
    return Getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'simplecontent';
  }
  
  public function  httpheader() {
    return turlmap::htmlheader(false);
  }
  
public function request($arg) {}
public function gettitle() {}
  
  public function getcont() {
    $result = empty($this->text) ? $this->html : sprintf("<h2>%s</h2>\n", $this->text);
    return $this->view->theme->simple($result);
  }
  
  public static function html($content) {
    $class = __class__;
    $self = self::i();
    $self->html = $content;
    $template = ttemplate::i();
    return $template->request($self);
  }
  
  public static function content($content) {
    $self = self::i();
    $self->text = $content;
    $template = ttemplate::i();
    return $template->request($self);
  }
  
  public static function gettheme() {
    return tview::getview(self::i())->theme;
  }
  
}//class