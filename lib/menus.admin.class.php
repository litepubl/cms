<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tadminmenus extends tmenus {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'adminmenu';
    $this->addevents('onexclude');
    $this->data['heads'] = '';
  }
  
  public function settitle($id, $title) {
    if ($id && isset($this->items[$id])) {
      $this->items[$id]['title'] = $title;
      $this->save();
      litepublisher::$urlmap->clearcache();
    }
  }
  
  public function getdir() {
    return litepublisher::$paths->data . 'adminmenus' . DIRECTORY_SEPARATOR;
  }
  
  public function getadmintitle($name) {
    $lang = tlocal::i();
    $ini = &$lang->ini;
    if (isset($ini[$name]['title'])) return $ini[$name]['title'];
    tlocal::usefile('install');
    if (!in_array('adminmenus', $lang->searchsect)) array_unshift($lang->searchsect, 'adminmenus');
    if ($result = $lang->__get($name)) return $result;
    return $name;
  }
  
  public function createurl($parent, $name) {
    return $parent == 0 ? "/admin/$name/" : $this->items[$parent]['url'] . "$name/";
  }
  
  public function createitem($parent, $name, $group, $class) {
    $title = $this->getadmintitle($name);
    $url = $this->createurl($parent, $name);
    return $this->additem(array(
    'parent' => $parent,
    'url' => $url,
    'title' => $title,
    'name' => $name,
    'class' => $class,
    'group' => $group
    ));
  }
  
  public function additem(array $item) {
    if (empty($item['group'])) {
      $groups = tusergroups::i();
      $item['group'] = $groups->items[$groups->defaults[0]]['name'];
    }
    return parent::additem($item);
  }
  
  public function addfakemenu(tmenu $menu) {
    $this->lock();
    $id = parent::addfakemenu($menu);
    if (empty($this->items[$id]['group'])) {
      $groups = tusergroups::i();
      $group = count($groups->defaults)  ? $groups->items[$groups->defaults[0]]['name'] : 'commentator';
      $this->items[$id]['group'] = $group;
    }
    
    $this->unlock();
    return $id;
  }
  
  public function getchilds($id) {
    if ($id == 0) {
      $result = array();
      $options = litepublisher::$options;
      foreach ($this->tree as $iditem => $items) {
        if ($options->hasgroup($this->items[$iditem]['group']))
        $result[] = $iditem;
      }
      return $result;
    }
    
    $parents = array($id);
    $parent = $this->items[$id]['parent'];
    while ($parent != 0) {
      array_unshift ($parents, $parent);
      $parent = $this->items[$parent]['parent'];
    }
    
    $tree = $this->tree;
    foreach ($parents as $parent) {
      foreach ($tree as $iditem => $items) {
        if ($iditem == $parent) {
          $tree = $items;
          break;
        }
      }
    }
    return array_keys($tree);
  }
  
  public function exclude($id) {
    if (!litepublisher::$options->hasgroup($this->items[$id]['group'])) return  true;
    return $this->onexclude($id);
  }
  
}//class
