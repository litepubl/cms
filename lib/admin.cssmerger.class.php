<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincssmerger extends tadminjsmerger {
  
  public static function i($id = 0) {
    return self::iteminstance(__class__, $id);
  }
  
  public function getmerger() {
    return tcssmerger::i();
  }
  
}//class