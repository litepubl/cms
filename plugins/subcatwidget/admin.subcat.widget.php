<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsubcatwidget extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $widget = tsubcatwidget::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $html= $this->html;
    $args = targs::i();
    $id = (int) tadminhtml::getparam('idwidget', 0);
    if (isset($widget->items[$id])) {
      $args->add($widget->items[$id]);
      $args->sort = tadminhtml::array2combo(tlocal::admin()->ini['sortnametags'], $widget->items[$id]['sortname']);
      $args->idwidget = $id;
      $args->data['$lang.invertorder'] = $about['invertorder'];
      $args->formtitle = $widget->gettitle($id);
      return $html->adminform('
      [combo=sort]
      [checkbox=showsubitems]
      [checkbox=showcount]
      [text=maxcount]
      [hidden=idwidget]',
      $args);
    }
    $tags = array();
    foreach ($widget->items as $id => $item) {
      $tags[] = $item['idtag'];
    }
    $args->formtitle = $about['formtitle'];
    return $html->adminform(tposteditor::getcategories($tags), $args);
  }
  
  public function processform()  {
    $widget = tsubcatwidget::i();
    $id = (int) tadminhtml::getparam('idwidget', 0);
    if (isset($widget->items[$id])) {
      $item = $widget->items[$id];
      extract($_POST, EXTR_SKIP);
      $item['maxcount'] = (int) $maxcount;
      $item['showcount'] = isset($showcount);
      $item['showsubitems'] = isset($showsubitems);
      $item['sortname'] = $sort;
      $widget->items[$id] = $item;
      $widget->save();
      return '';
    }
    
    $tags = array();
    foreach ($widget->items as $id => $item) {
      $tags[] = $item['idtag'];
    }
    $list = tposteditor::processcategories();
    $add = array_diff($list, $tags);
    $delete  = array_diff($tags, $list);
    if ((count($add) == 0) && (count($delete) == 0)) return '';
    $widget->lock();
    foreach ($delete as $idtag) {
      $widget->tagdeleted($idtag);
    }
    
    foreach ($add as $idtag) {
      $widget->add($idtag);
    }
    $widget->unlock();
  }
  
}//class