<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tsidebars extends tdata {
  public $items;

  public static function i($idview = 0) {
    $result = getinstance(__class__);
    if ($idview > 0) {
      $view = tview::i((int)$idview);
      $result->items = & $view->sidebars;
    }
    return $result;
  }

  protected function create() {
    parent::create();
    $view = tview::i();
    $this->items = & $view->sidebars;
  }

  public function load() {
  }

  public function save() {
    tview::i()->save();
  }

  public function add($id) {
    $this->insert($id, false, 0, -1);
  }

  public function insert($id, $ajax, $index, $order) {
    if (!isset($this->items[$index])) return $this->error("Unknown sidebar $index");
    $item = array(
      'id' => $id,
      'ajax' => $ajax
    );
    if (($order < 0) || ($order > count($this->items[$index]))) {
      $this->items[$index][] = $item;
    } else {
      array_insert($this->items[$index], $item, $order);
    }
    $this->save();
  }

  public function remove($id) {
    if ($pos = self::getpos($this->items, $id)) {
      array_delete($this->items[$pos[0]], $pos[1]);
      $this->save();
      return $pos[0];
    }
  }

  public function delete($id, $index) {
    if ($i = $this->indexof($id, $index)) {
      array_delete($this->items[$index], $i);
      $this->save();
      return $i;
    }
    return false;
  }

  public function deleteclass($classname) {
    if ($id = twidgets::i()->class2id($classname)) {
      tviews::i()->widgetdeleted($id);
    }
  }

  public function indexof($id, $index) {
    foreach ($this->items[$index] as $i => $item) {
      if ($id == $item['id']) return $i;
    }
    return false;
  }

  public function setajax($id, $ajax) {
    foreach ($this->items as $index => $items) {
      if ($pos = $this->indexof($id, $index)) {
        $this->items[$index][$pos]['ajax'] = $ajax;
      }
    }
  }

  public function move($id, $index, $neworder) {
    if ($old = $this->indexof($id, $index)) {
      if ($old != $newindex) {
        array_move($this->items[$index], $old, $neworder);
        $this->save();
      }
    }
  }

  public static function getpos(array & $sidebars, $id) {
    foreach ($sidebars as $i => $sidebar) {
      foreach ($sidebar as $j => $item) {
        if ($id == $item['id']) {
          return array(
            $i,
            $j
          );
        }
      }
    }
    return false;
  }

  public static function setpos(array & $items, $id, $newsidebar, $neworder) {
    if ($pos = self::getpos($items, $id)) {
      list($oldsidebar, $oldorder) = $pos;
      if (($oldsidebar != $newsidebar) || ($oldorder != $neworder)) {
        $item = $items[$oldsidebar][$oldorder];
        array_delete($items[$oldsidebar], $oldorder);
        if (($neworder < 0) || ($neworder > count($items[$newsidebar]))) $neworder = count($items[$newsidebar]);
        array_insert($items[$newsidebar], $item, $neworder);
      }
    }
  }

  public static function fix() {
    $widgets = twidgets::i();
    foreach ($widgets->classes as $classname => & $items) {
      foreach ($items as $i => $item) {
        if (!isset($widgets->items[$item['id']])) unset($items[$i]);
      }
    }

    $views = tviews::i();
    foreach ($views->items as & $viewitem) {
      if (($viewitem['id'] != 1) && !$viewitem['customsidebar']) continue;
      unset($sidebar);
      foreach ($viewitem['sidebars'] as & $sidebar) {
        for ($i = count($sidebar) - 1; $i >= 0; $i--) {
          //echo $sidebar[$i]['id'], '<br>';
          if (!isset($widgets->items[$sidebar[$i]['id']])) {
            array_delete($sidebar, $i);
          }
        }
      }
    }
    $views->save();
  }

} //class