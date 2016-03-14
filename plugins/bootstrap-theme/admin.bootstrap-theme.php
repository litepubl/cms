<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class admin_bootstrap_theme extends tadminmenu {

  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  public function getcontent() {
    $result = '';
    $theme = $this->theme;
    $admintheme = $this->admintheme;

    $lang = tlocal::admin('adminbootstraptheme');
    $args = new targs();

    $mainsidebars = array(
      'left' => $lang->left,
      'right' => $lang->right,
    );

    $views = tviews::i();
    foreach ($views->items as $id => $item) {
      if (!isset($item['custom']['mainsidebar'])) continue;

      $result.= $admintheme->h($item['name']);
      $result.= $theme->getinput('combo', "mainsidebar-$id", tadminhtml::array2combo($mainsidebars, $item['custom']['mainsidebar']) , $lang->mainsidebar);

      $result.= $theme->getinput('combo', "cssfile-$id", tadminhtml::array2combo($lang->ini['subthemes'], $item['custom']['cssfile']) , $lang->cssfile);

      $result.= '<hr>';
    }

    $args->formtitle = $lang->customizeview;
    return $admintheme->form($result, $args);
  }

  public function processform() {
    $lang = tlocal::admin('adminbootstraptheme');
    $views = tviews::i();
    foreach ($views->items as $id => $item) {
      if (!isset($item['custom']['mainsidebar'])) continue;

      $sidebar = $_POST["mainsidebar-$id"];
      if (!in_array($sidebar, array(
        'left',
        'right'
      ))) $sidebar = 'left';
      $views->items[$id]['custom']['mainsidebar'] = $sidebar;

      $cssfile = $_POST["cssfile-$id"];
      if (!isset($lang->ini['subthemes'][$cssfile])) $cssfile = 'default';
      $views->items[$id]['custom']['cssfile'] = $cssfile;
    }

    $views->save();
    ttheme::clearcache();
  }

} //class