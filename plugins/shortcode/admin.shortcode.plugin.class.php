<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tadminshortcodeplugin {

  public static function i() {
    return getinstance(__class__);
  }

  public function getcontent() {
    $plugin = tshortcode::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::i();

    $s = '';
    foreach ($plugin->items as $name => $value) {
      $s.= "$name = $value\n";
    }

    $args->codes = $s;
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.codes'] = $about['codes'];

    $html = tadminhtml::i();
    return $html->adminform('[editor=codes]', $args);
  }

  public function processform() {
    $plugin = tshortcode::i();
    //$plugin->setcodes($_POST['codes']);
    $plugin->items = tini2array::parsesection($_POST['codes']);
    $plugin->save();
  }

} //class