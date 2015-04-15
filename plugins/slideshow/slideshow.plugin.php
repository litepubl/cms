<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tslideshowplugin extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function install() {
    $home = thomepage::i();
    $home->content = $this->gethtml()  . $home->rawcontent;
    $home->save();
    litepublisher::$urlmap->clearcache();
  }
  
  public function uninstall() {
    $home = thomepage::i();
    $html = $this->gethtml();
    $home->content = str_replace($html, '', $home->rawcontent);
    $home->save();
    litepublisher::$urlmap->clearcache();
  }
  
  public function gethtml() {
    $template = ttemplate::i();
    $s = $template->getjavascript('/plugins/slideshow/slideshow.min.js');
    $about = tplugins::getabout(tplugins::getname(__file__));
    $s .= $about['html'];
    return sprintf('[html]%s[/html]', $s);
  }
  
}//class