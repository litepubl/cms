<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcolorpicker extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function install() {
    $parser = tthemeparser::i();
    $parser->parsed = $this->themeparsed;
    
    $jsmerger = tjsmerger::i();
    $jsmerger->add('admin', '/plugins/colorpicker/colorpicker.plugin.min.js');
  }
  
  public function uninstall() {
    $parser = tthemeparser::i();
    $parser->unbind($this);
    
    $jsmerger = tjsmerger::i();
    $jsmerger->deletefile('admin', '/plugins/colorpicker/colorpicker.plugin.min.js');
  }
  
  public function themeparsed(ttheme $theme) {
    if (empty($theme->templates['content.admin.color'])) {
      $about = tplugins::getabout('colorpicker');
      $theme->templates['content.admin.color'] =
      '<p>
      <input type="text" name="$name" id="text-$name" value="$value" size="22" />
      <label for="text-$name"><strong>$lang.$name</strong></label>
      <input type="button" name="colorbutton-$name" id="colorbutton-$name" rel="text-$name"
      value="' . $about['changecolor'] . '" />
      </p>';
    }
  }
  
}//class