<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class bootstrap_theme extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  public function themeparsed(ttheme $theme) {
    if ($theme->name != 'shop') return;
    
    //fix prop templates
    foreach (array('shop.product.props.prop', 'shop.product.props.prop.values') as $k) {
      $theme->templates[$k] = str_replace('idprop=', 'idprop$idprop=', $theme->templates[$k]);
    }
    
    $theme->templates['shop.product.props.singleprop'] = str_replace('entitle=', '$entitle=', $theme->templates['shop.product.props.singleprop']);
    
    $t = ttemplate::i();
    $t->data['themecolor'] = $this->color;
    
    $theme->templates['index'] = str_replace(
    '$custom.mainsidebar',
    $this->sidebar == 'left' ? 'left' : 'right',
    $theme->templates['index']);
    
    $parser = tthemeparser::i();
    if ($parser->stylebefore && !strpos($theme->templates['index'], '$template.cssmerger_default')) {
      //insert css merger before theme css
      $index = $theme->templates['index'];
      if ($i = strpos($index, '.css')) {
        $i = strrpos(substr($index, 0, $i), '<');
        $css = '<link type="text/css" href="$site.files$template.cssmerger_default" rel="stylesheet" />';
        $theme->templates['index'] = substr_replace($index, $css, $i - 1, 0);
        //fix $template.head
        if ((false !== strpos($t->heads, $css)) && (false === strpos($t->heads, "<!--$css-->"))) $t->heads = str_replace($css, "<!--$css-->", $t->heads);
      }
    }
    
    $t->save();
  }
  
  public function setpaths() {
    $this->externalfunc(get_class($this), 'setpaths', false);
    ttheme::clearcache();
  }
  
}//class