<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tsourcefilesmenu extends tmenu {
  
  public static function i($id = 0) {
    return $id == 0 ? self::singleinstance(__class__) : self::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = parent::getcontent();
    $plugin = tsourcefiles::i();
    $result .= $plugin->getcachecontent('', '');
    return $result;
  }
  
}//class