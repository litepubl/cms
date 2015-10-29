<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tcommentspool extends tpoolitems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'commentspool';
    $this->perpool = 50;
  }
  
  public function getitem($id) {
    return $this->getdb('posts')->getvalue($id, 'commentscount');
  }
  
}//class