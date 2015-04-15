<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcustomwidget extends twidget {
  public $items;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename   = 'widgets.custom';
    $this->adminclass = 'tadmincustomwidget';
    $this->addmap('items', array());
    $this->addevents('added', 'deleted');
  }
  
  public function getwidget($id, $sidebar) {
    if (!isset($this->items[$id])) return '';
    $item = $this->items[$id];
    if ($item['template'] == '') return $item['content'];
    $theme = ttheme::i();
    return $theme->getwidget($item['title'], $item['content'], $item['template'], $sidebar);
  }
  
  public function gettitle($id) {
    return $this->items[$id]['title'];
  }
  
  public function getcontent($id, $sidebar) {
    return $this->items[$id]['content'];
  }
  
  public function add($idview, $title, $content, $template) {
    $widgets = twidgets::i();
    $widgets->lock();
    $id = $widgets->addext($this, $title, $template);
    $this->items[$id] = array(
    'title' => $title,
    'content' => $content,
    'template' => $template
    );
    
    $sidebars = tsidebars::i($idview);
    $sidebars->add($id);
    $widgets->unlock();
    $this->save();
    $this->added($id);
    return $id;
  }
  
  public function edit($id, $title, $content, $template) {
    $this->items[$id] = array(
    'title' => $title,
    'content' => $content,
    'template' => $template
    );
    $this->save();
    
    $widgets = twidgets::i();
    $widgets->items[$id]['title'] = $title;
    $widgets->save();
    $this->expired($id);
    litepublisher::$urlmap->clearcache();
  }
  
  public function delete($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
      
      $widgets = twidgets::i();
      $widgets->delete($id);
      $this->deleted($id);
    }
  }
  
  public function widgetdeleted($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
    }
  }
  
} //class