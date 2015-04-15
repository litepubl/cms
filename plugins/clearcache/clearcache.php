<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tclearcache extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function clearcache() {
    tfiler::delete(litepublisher::$paths->data . 'themes', false, false);
    litepublisher::$urlmap->clearcache();
  }
  
  public function themeparsed(ttheme $theme) {
    $name = $theme->name;
    $views = tviews::i();
    foreach ($views->items as &$itemview) {
      if ($name == $itemview['themename']) {
        $itemview['custom'] = $theme->templates['custom'];
      }
    }
    $views->save();
  }
  
}//class