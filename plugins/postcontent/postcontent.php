<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpostcontentplugin extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['before'] = '';
    $this->data['after'] = '';
  }
  
  public function beforecontent($post, &$content) {
    $content = $this->before . $content;
  }
  
  public function aftercontent($post, &$content) {
    $content .= $this->after;
  }
  
}//class