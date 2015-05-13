<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tlazybuttons extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function themeparsed(ttheme $theme) {
    if (!strpos($theme->templates['content.post'], 'lazybuttons')) {
      $theme->templates['content.post'] = str_replace('$post.content', '$post.content <div class="lazybuttons"></div>', $theme->templates['content.post']);
    }
    
    if (!strpos($theme->templates['content.menu'], 'lazybuttons')) {
      $theme->templates['content.menu'] = str_replace('$menu.content', '$menu.content <div class="lazybuttons"></div>', $theme->templates['content.menu']);
    }
  }
  
}//class