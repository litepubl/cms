<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcategoriesmenu extends tplugin {
  public $tree;
  public $exitems;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addmap('tree', array());
    $this->addmap('exitems', array());
  }
  
  public function getmenu($hover, $current) {
    $result = '';
    $categories = tcategories::i();
    $categories->loadall();
    //$this->buildtree();
    //var_dump($this->tree);
    if (count($this->tree) > 0) {
      $theme = ttheme::i();
      if ($hover) {
        $items = $this->getsubmenu($this->tree, $current);
      } else {
        $items = '';
        $tml = $theme->templates['menu.item'];
        $args = targs::i();
        $args->submenu = '';
        foreach ($this->tree as $id => $subitems) {
          if ($this->exclude($id)) continue;
          $args->add($categories->items[$id]);
          $items .= $current == $id ? $theme->parsearg($theme->templates['menu.current'], $args) : $theme->parsearg($tml, $args);
        }
      }
      
      $result = str_replace('$item', $items, $theme->templates['menu']);
    }
    return $result;
  }
  
  public function exclude($id) {
    return in_array($id, $this->exitems);
  }
  
  private function getsubmenu(&$tree, $current) {
    $result = '';
    $categories = tcategories::i();
    $theme = ttheme::i();
    $tml = $theme->templates['menu.item'];
    $tml_submenu = $theme->templates['menu.item.submenu'];
    $args = targs::i();
    foreach ($tree as $id => $items) {
      if ($this->exclude($id)) continue;
      $submenu = '' ;
      if ((count($items) > 0) && ($s = $this->getsubmenu($items, $current))) {
        $submenu = str_replace('$items', $s, $tml_submenu);
      }
      $args->submenu = $submenu;
      $args->add($categories->items[$id]);
      $result .= $theme->parsearg($current == $id ?  $theme->templates['menu.current'] : $tml, $args);
    }
    return $result;
  }
  
  public function buildtree() {
    $categories = tcategories::i();
    $categories->loadall();
    $this->tree = $this->getsubtree(0);
    //var_dump($this->exitems );
    $this->exitems = array_intersect(array_keys($categories->items), $this->exitems);
    $this->save();
  }
  
  private function getsubtree($parent) {
    $result = array();
    $categories = tcategories::i();
    // first step is a find all childs and sort them
    $sort= array();
    foreach ($categories->items as $id => $item) {
      if ($item['parent'] == $parent) {
        $sort[$id] = (int) $item['customorder'];
      }
    }
    arsort($sort, SORT_NUMERIC);
    $sort = array_reverse($sort, true);
    
    foreach ($sort as $id => $order) {
      $result[$id]  = $this->getsubtree($id);
    }
    return $result;
  }
  
}//class