<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsingletagwidget  extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $widget = tsingletagwidget::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $html= $this->html;
    $args = targs::i();
    $id = (int) tadminhtml::getparam('idwidget', 0);
    if (isset($widget->items[$id])) {
      $args->add($widget->items[$id]);
      $args->idwidget = $id;
      $args->data['$lang.invertorder'] = $about['invertorder'];
      $args->formtitle = $widget->gettitle($id);
      return $html->adminform('[text=maxcount]
      [checkbox=invertorder]
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
    $widget = tsingletagwidget::i();
    $id = (int) tadminhtml::getparam('idwidget', 0);
    if (isset($widget->items[$id])) {
      $widget->items[$id]['maxcount'] = (int) $_POST['maxcount'];
      $widget->items[$id]['invertorder'] = isset( $_POST['invertorder']);
      $widget->save();
      litepublisher::$urlmap->clearcache();
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
    litepublisher::$urlmap->clearcache();
  }
  
}//class