<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class adminparser extends baseparser  {
  public $themefiles;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'admimparser';
    $this->tagfiles[] = 'themes/admin/admintags.ini';
    $this->addmap('themefiles', array());
  }
  
  public function getfilelist($name) {
    $result = parent::getfilelist($name);
    return $result + $this->themefiles;
  }
  
}//class