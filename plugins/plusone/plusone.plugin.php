<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tplusoneplugin extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function install() {
    $template = ttemplate::i();
    $template->addtohead($this->getjs());
    
    $parser = tthemeparser::i();
    $parser->parsed = $this->themeparsed;
    ttheme::clearcache();
  }
  
  public function uninstall() {
    $template = ttemplate::i();
    $template->deletefromhead($this->getjs());
    
    $parser = tthemeparser::i();
    $parser->unbind($this);
    ttheme::clearcache();
  }
  
  public function themeparsed(ttheme $theme) {
    if (strpos($theme->templates['content.post'], 'g-plusone')) return;
    $theme->templates['content.post'] = str_replace('$post.content', '$post.content' .
    '<div class="g-plusone"></div>',
    $theme->templates['content.post']);
  }
  
  public function getjs() {
  $lang = litepublisher::$options->language == 'en' ? '' : sprintf('{lang: \'%s\'}', litepublisher::$options->language);
    return '<script type="text/javascript" src="https://apis.google.com/js/plusone.js">'. $lang . '</script>' ;
  }
  
}//class