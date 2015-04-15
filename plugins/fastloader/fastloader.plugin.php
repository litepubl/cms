<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfastloader extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function install() {
    $parser = tthemeparser::i();
    $parser->parsed = $this->themeparsed;
    ttheme::clearcache();
    
    $template = ttemplate::i();
    $template->js = '<script type="text/javascript">jqloader.load("%s");</script>';
  $template->jsready = '<script type="text/javascript">jqloader.ready(function() {%s});</script>';
    $template->jsload = '<script type="text/javascript">jqloader.load(%s);</script>';
    $template->heads = $this->replacehead($template->heads);
    $template->save();
    
    $admin = tadminmenus::i();
    $admin->heads = $this->replace($admin->heads);
    $admin->save();
    ttheme::clearcache();
  }
  
  public function uninstall() {
    $template = ttemplate::i();
    $template->js = '<script type="text/javascript" src="%s"></script>';
  $template->jsready = '<script type="text/javascript">$(document).ready(function() {%s});</script>';
    $template->jsload = '<script type="text/javascript">$.getScript(%s);</script>';
    $template->heads = $this->restorehead($template->heads);
    $template->save();
    
    $parser = tthemeparser::i();
    $parser->unsubscribeclass($this);
    $admin = tadminmenus::i();
    $admin->heads = $this->restore($admin->heads);
    $admin->save();
    ttheme::clearcache();
  }
  
  public function themeparsed($theme) {
    $template = ttemplate::i();
    $template->heads = $this->replacehead($template->heads);
    $template->save();
    
    foreach ($theme->templates as $name => $value) {
      if (is_string($value))
      $theme->templates[$name] = $this->replace($value);
    }
  }
  
  public function replace($s) {
    $s = preg_replace('/<script\s*.*?src\s*=\s*[\'"]([^"\'>]*).*?>\s*<\/script>/im',
    '<script type="text/javascript">jqloader.load("$1");</script>', $s);
    
    $s = preg_replace('/\$\s*\(\s*document\s*\)\s*\.\s*ready\s*\(/im',
    'jqloader.ready(', $s);
    
    $s = preg_replace('/\$\.\s*getScript\s*\(/im',
    'jqloader.load(', $s);
    
    $s = str_replace('$.load_script', 'jqloader.load', $s);
    return $s;
  }
  
  public function restore($s) {
    $s = preg_replace(
    str_replace(' ', '\s*',
    '/<script.*> jqloader \. load \( [\'"]([^"\']*).*?\) ; <\/script>/im'),
    '<script type="text/javascript" src="$1"></script>', $s);
    
    $s = str_replace('jqloader.ready', '$(document).ready', $s);
    //$s = str_replace('jqloader.load', '$.getScript', $s);
    $s = str_replace('jqloader.load', '$.load_script', $s);
    return $s;
  }
  
  public function replacehead($s) {
    $script = '<script type="text/javascript" src="$site.files$template.jsmerger_default"></script>';
    if ($i = strpos($s, $script)) {
      return substr($s, 0, $i) .
      '<script type="text/javascript" src="$site.files/js/litepublisher/loader.min.js"></script>' .
      '<script type="text/javascript">jqloader.load_jquery("$site.files$template.jsmerger_default");</script>' .
      $this->replace(substr($s, $i + strlen($script)));
    } else {
      return $s;
    }
  }
  
  public function restorehead($s) {
    $script = '<script type="text/javascript" src="$site.files/js/litepublisher/loader.min.js"></script>' .
    '<script type="text/javascript">jqloader.load_jquery("$site.files$template.jsmerger_default");</script>';
    if ($i = strpos($s, $script)) {
      return substr($s, 0, $i) .
      '<script type="text/javascript" src="$site.files$template.jsmerger_default"></script>' .
      $this->restore(substr($s, $i + strlen($script)));
    } else {
      return $s;
    }
  }
  
}//class