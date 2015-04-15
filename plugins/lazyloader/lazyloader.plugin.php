<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlazyloader extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function install() {
    $parser = tthemeparser::i();
    $parser->parsed = $this->themeparsed;
    
    $template = ttemplate::i();
    $template->js = '<script type="text/javascript">$.load_script("%s");</script>';
    $template->save();
    
    $admin = tadminmenus::i();
    $admin->heads = $this->replace($admin->heads);
    $admin->save();
    ttheme::clearcache();
  }
  
  public function uninstall() {
    $template = ttemplate::i();
    $template->js = '<script type="text/javascript" src="%s"></script>';
    $template->save();
    
    $parser = tthemeparser::i();
    $parser->unbind($this);
    $admin = tadminmenus::i();
    $admin->heads = $this->restore($admin->heads);
    $admin->save();
    ttheme::clearcache();
  }
  
  public function themeparsed($theme) {
    foreach ($theme->templates as $name => $value) {
      if (is_string($value))
      $theme->templates[$name] = $this->replace($value);
    }
  }
  
  public function replace($s) {
    return preg_replace('/<script\s*.*?src\s*=\s*[\'"]([^"\'>]*).*?>\s*<\/script>/im',
    '<script type="text/javascript">$.load_script("$1");</script>', $s);
  }
  
  public function restore($s) {
    return preg_replace(
    str_replace(' ', '\s*',
    '/<script.*> \$\. load_script \( [\'"]([^"\']*).*?\) ; <\/script>/im'),
    '<script type="text/javascript" src="$1"></script>', $s);
  }
  
}//class