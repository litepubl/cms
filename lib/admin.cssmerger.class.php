<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tadmincssmerger extends tadminjsmerger {

  public static function i($id = 0) {
    return static::iteminstance(__class__, $id);
  }

  public function getmerger() {
    return tcssmerger::i();
  }

} //class