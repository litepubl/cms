<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpagenator3000 extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function themeparsed(ttheme $theme) {
    $tag = '<div class="hidden next-paginator"></div>';
    if (false !== strpos($theme->templates['content.navi'], $tag)) return;
    
    $script = '<script type="text/javascript">
    $(document).ready(function() {
      $(".next-paginator").nextpaginator({
        count: $count,
        perpage: $perpage,
        page: $page,
        url: "$link",
        pageurl: "$pageurl"
      });
    });
    </script>';
    
    $theme->templates['content.navi'] = $tag . $theme->templates['content.navi'] .$script;
  }
  
}//class