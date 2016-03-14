<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tadmincategoriesmenu {

  public static function i() {
    return getinstance(__class__);
  }

  public function getcontent() {
    $plugin = tcategoriesmenu::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = new targs();
    $args->cats = admintheme::i()->getcats($plugin->exitems);
    $args->formtitle = $about['formtitle'];
    //    $args->data['$lang.before'] = $about['before'];
    $html = tadminhtml::i();
    return $html->adminform('$cats', $args);
  }

  public function processform() {
    $plugin = tcategoriesmenu::i();
    $plugin->exitems = tadminhtml::check2array('category-');
    $plugin->save();
  }

} //class