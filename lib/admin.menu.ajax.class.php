<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tajaxmenueditor extends tajaxposteditor {

  public static function i() {
    return getinstance(__class__);
  }

  public function install() {
    litepubl::$urlmap->addget('/admin/ajaxmenueditor.htm', get_class($this));
  }

  public function request($arg) {
    if ($err = static::auth()) return $err;
    return $this->getcontent();
  }

  public function getcontent() {
    $id = tadminhtml::idparam();
    $menus = tmenus::i();
    if (($id != 0) && !$menus->itemexists($id)) return static::error403();
    $menu = tmenu::i($id);
    if ((litepubl::$options->group == 'author') && (litepubl::$options->user != $menu->author)) return static::error403();
    if (($id > 0) && !$menus->itemexists($id)) return static::error403();

    $views = tviews::i();
    $theme = tview::i($views->defaults['admin'])->theme;
    $html = tadminhtml::i();
    $html->section = 'menu';

    switch ($_GET['get']) {
      case 'view':
        $result = tadminviews::getcomboview($id == 0 ? $views->defaults['menu'] : $menu->idview);
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

} //class