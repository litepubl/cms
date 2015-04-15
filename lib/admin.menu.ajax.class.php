<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tajaxmenueditor extends tajaxposteditor  {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function install() {
    litepublisher::$urlmap->addget('/admin/ajaxmenueditor.htm', get_class($this));
  }
  
  public function request($arg) {
    if ($err = self::auth()) return $err;
    return $this->getcontent();
  }
  
  public function getcontent() {
    $id = tadminhtml::idparam();
    $menus = tmenus::i();
    if (($id != 0) && !$menus->itemexists($id)) return self::error403();
    $menu = tmenu::i($id);
    if ((litepublisher::$options->group == 'author') && (litepublisher::$options->user != $menu->author)) return self::error403();
    if (($id > 0) && !$menus->itemexists($id)) return self::error403();
    
    $views = tviews::i();
    $theme = tview::i($views->defaults['admin'])->theme;
    $html = tadminhtml ::i();
    $html->section = 'menu';
    
    switch ($_GET['get']) {
      case 'view':
      $result = tadminviews::getcomboview($id == 0 ?  $views->defaults['menu'] : $menu->idview);
      break;
      
      case 'seo':
      $args = targs::i();
      $args->url = $menu->url;
      $args->keywords = $menu->keywords;
      $args->description = $menu->description;
      $args->head = $menu->data['head'];
      $result = $html->parsearg('[text=url] [text=description] [text=keywords] [editor=head]', $args);
      break;
      
      default:
      $result = var_export($_GET, true);
    }
    return turlmap::htmlheader(false) . $result;
  }
  
}//class