<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmintagreplacer {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function  gethead() {
    return tuitabs::gethead();
  }
  
  public function getcontent() {
    $plugin = ttagreplacer ::i();
    $html = tadminhtml::i();
    $tabs = new tuitabs();
    $args = targs::i();
    $about = tplugins::getabout('tagreplacer');
    $args->formtitle = $about['name'];
    
    $tabs->add($about['new'], $html->getinput('text',
    'where-add', '', $about['where']) .
    $html->getinput('text',
    'search-add', '', $about['search']) .
    $html->getinput('editor',
    'replace-add', '', $about['replace']) );
    
    foreach ($plugin->items as $i => $item) {
      $tabs->add($item['where'],
      $html->getinput('text',
      "where-$i", tadminhtml::specchars($item['where']), $about['where']) .
      $html->getinput('text',
      "search-$i", tadminhtml::specchars($item['search']), $about['search']) .
      $html->getinput('editor',
      "replace-$i", tadminhtml::specchars($item['replace']), $about['replace']) );
    }
    
    return $html->adminform($tabs->get(), $args);
  }
  
  public function processform() {
    $theme = tview::i(tviews::i()->defaults['admin'])->theme;
    $plugin = ttagreplacer ::i();
    $plugin->lock();
    $plugin->items = array();
    foreach ($_POST as $name => $value) {
      if (!strbegin($name, 'where-')) continue;
      $id = substr($name, strlen('where-'));
      $where = trim($value);
      if (!isset($theme->templates[$where]) || !is_string($theme->templates[$where])) continue;
      $search = $_POST["search-$id"];
      if ($search == '') continue;
      $plugin->items[] = array(
      'where' => $where,
      'search' => $search,
      'replace' => $_POST["replace-$id"]
      );
    }
    $plugin->unlock();
    ttheme::clearcache();
    return '';
  }
  
}//class