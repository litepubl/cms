<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TRSSPrevNext extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function beforepost($id, &$content) {
    $post = tpost::i($id);
    $content .= $post->prevnext;
  }
  
}//class