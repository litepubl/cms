<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsubcatwidget extends  twidget {
  public $items;
  public $tags;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->adminclass = 'tadminsubcatwidget';
    $this->basename = 'widget.subcat';
    $this->addmap('items', array());
    $this->tags = tcategories::i();
  }
  
  public function getidwidget($idtag) {
    foreach ($this->items as $id => $item) {
      if ($idtag == $item['idtag'])  return $id;
    }
    return false;
  }
  
  public function add($idtag) {
    $tag = $this->tags->getitem($idtag);
    $widgets = twidgets::i();
    $id = $widgets->addext($this, $tag['title'], 'categories');
    $this->items[$id] = array(
    'idtag' => $idtag,
    'sortname' => 'count',
    'showsubitems' => true,
    'showcount' => true,
    'maxcount' => 0,
    'template' => 'categories'
    );
    
    $sidebars = tsidebars::i();
    $sidebars->add($id);
    $this->save();
    //$this->added($id);
    return $id;
  }
  
  public function delete($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
      
      $widgets = twidgets::i();
      $widgets->delete($id);
      //$this->deleted($id);
    }
  }
  
  public function widgetdeleted($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
    }
  }
  
  public function tagdeleted($idtag) {
    if ($idwidget = $this->getidwidget($idtag)) return $this->delete($idwidget);
  }
  
  public function gettitle($id) {
    if (isset($this->items[$id])) {
      if ($tag = $this->tags->getitem($this->items[$id]['idtag'])) {
        return $tag['title'];
      }
    }
    return '';
  }
  
  public function getcontent($id, $sidebar) {
    if (!isset($this->items[$id])) return '';
    $item = $this->items[$id];
    $theme = ttheme::i();
    return $this->tags->getsortedcontent(
    array(
    'item' => $theme->getwidgetitem($item['template'], $sidebar),
    'subcount' =>$theme->getwidgettml($sidebar, $item['template'], 'subcount'),
    'subitems' => $item['showsubitems'] ? $theme->getwidgettml($sidebar, $item['template'], 'subitems') : '',
    ),
    $item['idtag'],
    $item['sortname'], $item['maxcount'], $item['showcount']);
  }
  
}//class