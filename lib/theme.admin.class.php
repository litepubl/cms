<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class admintheme extends basetheme {

  public static function i() {
    return getinstance(__class__);
  }

  public static function getinstance($name) {
return self::getbyname(__class__, $name);
}

public function getparser() {
return adminparser::i();
}

}//class