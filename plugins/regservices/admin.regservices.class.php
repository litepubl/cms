<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tadminregservices implements iadmin {

  public static function i() {
    return getinstance(__class__);
  }

  public function gethead() {
    return tuitabs::gethead();
  }

  public function getcontent() {
    $plugin = tregservices::i();
    $html = tadminhtml::i();
    $tabs = new tuitabs();
    $args = targs::i();
    $lang = tplugins::getnamelang($plugin->dirname);
    $args->formtitle = $lang->options;
    foreach ($plugin->items as $id => $classname) {
      $service = getinstance($classname);
      $tabs->add($service->title, $service->gettab($html, $args, $lang));
    }

    return $html->adminform($tabs->get() , $args);
  }

  public function processform() {
    $plugin = tregservices::i();
    $plugin->lock();
    foreach ($plugin->items as $name => $classname) {
      $service = getinstance($classname);
      $service->processform();
    }

    $plugin->update_widget();
    $plugin->unlock();
    return '';
  }

} //class