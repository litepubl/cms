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
foreach ($this->themefiles as $filename) {
$filename = ltrim($filename, '/');
if (!$filename) continue;

if (file_exists(litepublisher::$paths->home . $filename)) {
$result[] = litepublisher::$paths->home . $filename;
} else if (file_exists(litepublisher::$paths->themes . $filename)) {
$result[] = litepublisher::$paths->themes . $filename;
}
}

return $result;
  }

      public function loadpaths() {
if (!count($this->tagfiles)) {
$this->tagfiles[] = 'themes/admin/admintags.ini';
}

return parent::loadpaths();
}
  
}//class