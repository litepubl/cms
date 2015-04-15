<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmenuwidget extends tclasswidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->cache = 'nocache';
    $this->basename = 'widget.menu';
    $this->template = 'submenu';
    $this->adminclass = 'tadminorderwidget';
  }
  
  public function getdeftitle() {
    return tlocal::get('default', 'submenu');
  }
  
  public function getwidget($id, $sidebar) {
    $template = ttemplate::i();
    if ($template->hover) return '';
    $content = $this->getcontent($id, $sidebar);
    if ($content == '') return '';
    $title = $this->gettitle($id);
    $theme = ttheme::i();
    return $theme->getwidget($title, $content, $this->template, $sidebar);
  }
  
  public function gettitle($id) {
    if (litepublisher::$urlmap->context instanceof tmenu) return litepublisher::$urlmap->context->title;
    return parent::gettitle($id);
  }
  
  public function getcontent($idwidget, $sidebar) {
    $menu = $this->getcontext('tmenu');
    $id = $menu->id;
    $menus = $menu->owner;
    $result = '';
    $theme = ttheme::i();
    $tml = $theme->getwidgetitem('submenu', $sidebar);
    $subtml = $theme->getwidgettml($sidebar, 'submenu', 'subitems');
    // 1 submenu list
    $submenu = '';
    $childs = $menus->getchilds($id);
    foreach ($childs as $child) {
      $submenu .= $this->getitem($tml, $menus->getitem($child), '');
    }
    
    $parent = $menus->getparent($id);
    if ($parent == 0) {
      $result = $submenu;
    } else {
      if ($submenu != '') $submenu = str_replace($subtml, '$item', $submenu);
      $sibling = $menus->getchilds($parent);
      foreach ($sibling as $iditem) {
        $result .= $this->getitem($tml, $menus->getitem($iditem), $iditem == $id ? $submenu : '');
      }
    }
    
    $parents = $menus->getparents($id);
    foreach ($parents as $parent) {
      $result = str_replace($subtml, '$item', $result);
      $result = $this->getitem($tml, $menus->getitem($parent), $result);
    }
    
    if ($result == '')  return '';
    return $theme->getwidgetcontent($result, 'submenu', $sidebar);
  }
  
  private function getitem($tml, $item, $subnodes) {
    $args = targs::i();
    $args->add($item);
    $args->anchor = $item['title'];
    $args->rel = 'menu';
    $args->icon = '';
    $args->subcount = '';
    $args->subitems = $subnodes;
    $theme = ttheme::i();
    return $theme->parsearg($tml, $args);
  }
  
}//class