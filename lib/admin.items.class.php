<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class adminitems  {
  
  public static function getcontent($holder, $menu) {
    $result = '';
    $html = $menu->html;
    $lang = tlocal::admin();
    $id = (int) tadminhtml::getparam('id', 0);
    $args = new targs();
    $args->id = $id;
    $args->adminurl = $menu->adminurl;
    
    if (isset($_GET['action']) && ($_GET['action'] == 'delete') && $tags->itemexists($id)) {
      if  (isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1)) {
        $holder->delete($id);
        $result .= $html->h4->deleted;
      } else {
        return $html->confirmdelete($id, $menu->adminurl, $lang->confirmdelete);
      }
    }
    
    if ($id ==  0) {
      $item = $menu->defaultitem;
    } elseif ($holder->itemexists($id)) {
      $item = $holder->getitem($id);
    } else {
      $item = false;
    }
    
    if ($item) {
      $args->add($item);
      $menu->editargs($item, $args);
      $result .= $html->adminform($menu->editform, $args);
    }
    
    //table
    $perpage = 20;
    $count = $holder->count;
    $from = $menu->getfrom($perpage, $count);
    $items = $holder->select($menu->where, " order by id desc limit $from, $perpage");
    if (!$items) $items = array();
    
    $result .= $html->buildtable($items, $menu->table);
    
    $result = $html->fixquote($result);
    $theme = ttheme::i();
    $result .= $theme->getpages($menu->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public static function processform($holder, $menu) {
    $id = (int) tadminhtml::getparam('id', 0);
    if ($id == 0) {
      $item = $menu->defaultitem;
      foreach ($item as $k => $v) {
        if (isset($_POST[$k])) $item[$k] = $_POST[$k];
      }
      
      $id = $holder->db->add($item);
      $item['id'] = $id;
      $_POST['id'] = $id;
      $_GET['id'] = $id;
    } else {
      $item = $holder->getitem($id);
      foreach ($item as $k => $v) {
        if (isset($_POST[$k])) $item[$k] = $_POST[$k];
      }
      $item['id'] = $id;
      $holder->db->update($item);
    }
    
    $holder->items[$id] = $item;
  }
  
}//class