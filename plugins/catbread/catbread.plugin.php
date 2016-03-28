<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class catbread extends tplugin {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->data['showhome'] = true;
    $this->data['showchilds'] = true;
    $this->data['childsortname'] = 'title';
    $this->data['showsimilar'] = false;
    $this->data['breadpos'] = 'before';
    $this->data['similarpos'] = 'after';
  }

public function getcats() {
return tcategories::i();
}

  public function beforecat(&$result) {
    $cats = $this->cats;
    $idcat = $cats->id;
    if (!$idcat) {
    return $result;
}

    $result.= $this->getbread($idcat);

    if ($this->showsimilar) {
      $list = array();
      $idposts = $cats->getidposts($idcat);
      foreach ($idposts as $idpost) {
        $list = array_merge($list, tpost::i($idpost)->categories);
      }

      array_clean($list);
      array_delete_value($list, $idcat);
      $result.= $this->getsimilar($list);
    }

    return $result;
  }

  public function getpost() {
    $post = ttheme::$vars['post'];
    if (count($post->categories)) {
    return $this->getbread($post->categories[0]);
}

return '';
  }

  public function getsimilar() {
    if (!$this->showsimilar) {
return '';
}

    $post = ttheme::$vars['post'];
    if (count($post->categories)) {
    return $this->getsimilar($post->categories);
}

return '';
  }

  public function getread($idcat) {
    if (!$idcat) {
      return '';
    }

    $result = '';
    $cats = $this->cats;
    $cats->loadall();
    $parents = $cats->getparents($idcat);
    $parents = array_reverse($parents);

    $showchilds = false;
    if ($this->showchilds) {
      foreach ($cats->items as $id => $item) {
        if ($idcat == (int)$item['parent']) {
          $showchilds = true;
          break;
        }
      }
    }

    $theme = ttheme::i();
    $tml = $theme->templates['catbread.items.item'];
$lang = tlocal::i('catbreads');
    $args = new targs();
    $items = '';
    $index = 1;

    if ($this->showhome) {
      $args->url = '/';
      $args->title = $lang->home;
      $args->index = $index++;
      $items.= $theme->parsearg($tml, $args);
    }

    foreach ($parents as $id) {
      $args->add($cats->getitem($id));
      $args->index = $index++;
      $items.= $theme->parsearg($tml, $args);
    }

    $args->add($cats->getitem($idcat));
    $args->index = $index++;
    $current = $theme->parsearg($theme->templates['catbread.items.current'], $args);

$childs = '';
    if ($showchilds) {
$childs = $this->getchilds($idcat);
    }

$args->item = $items;
$args->current = $current;
$args->childs = $childs;
$args->items = $theme->parsearg($theme->templates['catbreads.items'], $args);
$args->similar = '';
    return $theme->parsearg($templates['catbreads'], $args;
  }

  public function getchilds($parent) {
    $cats = $this->cats;
    $sorted = $cats->getsorted($parent, $this->childsortname, 0);
    if (!count($sorted)) {
return '';
}

    $theme = ttheme::i();
    $tml = $theme->templates['catbreads.items.childs.item'];
    $args = new targs();
    $args->parent = $parent;

$items = '';
    foreach ($sorted as $id) {
      $args->add($cats->getitem($id));
      $items .= $theme->parsearg($tml, $args);
    }

$args->item = $items;
    return $theme->parsearg($theme->templates['catbreads.items.childs'], $args);
  }

  public function getsimilar($list) {
    if (!$this->showsimilar || !count($list)) return '';
    $cats = $this->cats;
    $cats->loadall();
    $parents = array();
    foreach ($list as $id) {
      $parents[] = $cats->getvalue($id, 'parent');
    }

    array_clean($parents);
    if (!count($parents)) return '';
    /* without db cant sort
    $similar = array();
    foreach ($cats->items as $id => $item) {
      if (in_array($item['parent'], $parents)) $similar[] = $id;
    }
    */
    $parents = implode(',', $parents);
    $list = implode(',', $list);
    $similar = $cats->db->idselect("parent in ($parents) and id not in ($list) order by $this->childsortname asc");
    array_clean($similar);
    if (!count($similar)) return '';

    $theme = ttheme::i();
    $args = new targs();
    $items = '';
    foreach ($similar as $id) {
      $args->add($cats->getitem($id));
      $items.= $theme->parsearg($this->tml['similaritem'], $args);
    }

    $args->item = $items;
    return $theme->parsearg($this->tml['similaritems'], $args);
  }

} //class