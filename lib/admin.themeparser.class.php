<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class adminthemeparser extends tadminmenu {

  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  public function gethead() {
    return parent::gethead() . tuitabs::gethead();
  }

  public function getcontent() {
    $html = $this->html;
    $lang = tlocal::i('options');
    $args = targs::i();
    $tabs = new tuitabs();

    $themeparser = tthemeparser::i();
    $args->tagfiles = implode("\n", $themeparser->tagfiles);
    $tabs->add($lang->theme, '[editor=tagfiles]');

    $admin = adminparser::i();
    $args->admintagfiles = implode("\n", $admin->tagfiles);
    $args->themefiles = implode("\n", $admin->themefiles);
    $tabs->add($lang->admin, '[editor=admintagfiles] [editor=themefiles]');

    $args->formtitle = $lang->options;
    return $html->adminform($tabs->get() , $args);
  }

  public function processform() {
    $themeparser = tthemeparser::i();
    $themeparser->tagfiles = strtoarray($_POST['tagfiles']);
    $themeparser->save();

    $admin = adminparser::i();
    $admin->tagfiles = strtoarray($_POST['admintagfiles']);
    $admin->themefiles = strtoarray($_POST['themefiles']);
    $admin->save();

    basetheme::clearcache();
  }

} //class