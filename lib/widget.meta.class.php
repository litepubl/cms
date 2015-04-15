<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmetawidget extends twidget {
  public $items;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.meta';
    $this->template = 'meta';
    $this->adminclass = 'tadminmetawidget';
    $this->addmap('items', array());
  }
  
  public function getdeftitle() {
    return tlocal::get('default', 'subscribe');
  }
  
  public function add($name, $url, $title) {
    $this->items[$name] = array(
    'enabled' => true,
    'url' => $url,
    'title' => $title
    );
    $this->save();
  }
  
  public function delete($name) {
    if (isset($this->items[$name])) {
      unset($this->items[$name]);
      $this->save();
    }
  }
  
  public function getcontent($id, $sidebar) {
    $result = '';
    $theme = ttheme::i();
    $tml = $theme->getwidgetitem('meta', $sidebar);
    $metaclasses = $theme->getwidgettml($sidebar, 'meta', 'classes');
    $args = targs::i();
    foreach    ($this->items as $name => $item) {
      if (!$item['enabled']) continue;
      $args->add($item);
      $args->icon = '';
      $args->subcount = '';
      $args->subitems = '';
      $args->rel = $name;
      if ($name == 'profile') {
        $args->rel = 'author profile';
      }
      $args->class = isset($metaclasses[$name]) ? $metaclasses[$name] : '';
      $result .= $theme->parsearg($tml, $args);
    }
    
    if ($result == '') return '';
    return $theme->getwidgetcontent($result, 'meta', $sidebar);
  }
  
}//class