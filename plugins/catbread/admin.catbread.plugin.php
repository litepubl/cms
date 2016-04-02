<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl\plugins;
use litepubl;

class admincatbread implements iadmin {

  public static function i() {
    return getinstance(__class__);
  }

  public function getcontent() {
    $plugin = catbread::i();
    $lang = tplugins::getnamelang('catbread');
    $admintheme = admintheme::i();
    $args = new targs();
    $args->showhome = $plugin->showhome;
    $args->showchilds = $plugin->showchilds;
    $args->showsimilar = $plugin->showsimilar;

    $lang->addsearch('sortnametags');
    $sort = array(
      'title' => $lang->title,
      'itemscount' => $lang->count,
      'customorder' => $lang->customorder,
    );

    $args->sort = tadminhtml::array2combo($sort, $plugin->childsortname);

    $pos = array(
      'replace' => $lang->replace,
      'top' => $lang->top,
      'before' => $lang->before,
      'after' => $lang->after,
      'nothing' => $lang->nothing,
    );

    $args->breadpos = tadminhtml::array2combo($pos, $plugin->breadpos);
    $args->similarpos = tadminhtml::array2combo($pos, $plugin->similarpos);

    $args->formtitle = $lang->formtitle;
    return $admintheme->form('
    [checkbox=showhome]
    [combo=breadpos]
    [checkbox=showchilds]
    [combo=sort]
    [checkbox=showsimilar]
    [combo=similarpos]
    ', $args);
  }

  public function processform() {
    extract($_POST, EXTR_SKIP);
    $plugin = catbread::i();
    $plugin->showhome = isset($showchilds);
    $plugin->showchilds = isset($showchilds);
    $plugin->showsimilar = isset($showsimilar);
    $plugin->childsortname = $sort;
    $plugin->breadpos = $breadpos;
    $plugin->similarpos = $similarpos;
    $plugin->save();
    basetheme::clearcache();
    return '';
  }

} //class