<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcommentspull extends tpullitems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'commentspull';
    $this->perpull = 50;
  }
  
  public function getitem($id) {
    return $this->getdb('posts')->getvalue($id, 'commentscount');
  }
  
}//class