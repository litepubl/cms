<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tadminjsmerger extends tadminmenu {

  public static function i($id = 0) {
    return self::iteminstance(__class__, $id);
  }

  public function getmerger() {
    return tjsmerger::i();
  }

  public function getcontent() {
    $merger = $this->getmerger();
      $tabs = new tabs($this->admintheme);
    $html = $this->html;
    $lang = tlocal::i('views');
    $args = targs::i();
    $args->formtitle = $this->title;
    foreach ($merger->items as $section => $items) {
      $tab = new tabs($this->admintheme);
      $tab->add($lang->files, $html->getinput('editor', $section . '_files', tadminhtml::specchars(implode("\n", $items['files'])) , $lang->files));
      foreach ($items['texts'] as $key => $text) {
        $tab->add($key, $html->getinput('editor', $section . '_text_' . $key, tadminhtml::specchars($text) , $key));
      }

      $tabs->add($section, $tab->get());
    }

    return $html->adminform($tabs->get() , $args);
  }

  public function processform() {
    $merger = $this->getmerger();
    $merger->lock();
    //$merger->items = array();
    //$merger->install();
    foreach (array_keys($merger->items) as $section) {
      $keys = array_keys($merger->items[$section]['texts']);
      $merger->setfiles($section, $_POST[$section . '_files']);
      foreach ($keys as $key) {
        $merger->addtext($section, $key, $_POST[$section . '_text_' . $key]);
      }
    }
    $merger->unlock();
  }

} //class