<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tsameposts extends tclasswidget {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    if (dbversion) {
      $this->table = 'sameposts';
    } else {
      $this->data['revision'] = 1;
    }

    $this->basename = 'widget.sameposts';
    $this->template = 'posts';
    $this->adminclass = 'tadminsameposts';
    $this->cache = 'nocache';
    $this->data['maxcount'] = 10;
  }

  public function getdeftitle() {
    return tlocal::get('default', 'sameposts');
  }

  public function postschanged() {
    if (dbversion) {
      $this->db->exec("truncate $this->thistable");
    } else {
      $this->revision+= 1;
      $this->save();
    }
  }

  private function findsame($idpost) {
    $posts = tposts::i();
    $post = tpost::i($idpost);
    if (count($post->categories) == 0) return array();
    $cats = tcategories::i();
    $cats->loadall();
    $same = array();
    foreach ($post->categories as $idcat) {
      if (!isset($cats->items[$idcat])) continue;
      $itemsposts = $cats->itemsposts->getposts($idcat);
      $itemsposts = $posts->stripdrafts($itemsposts);
      foreach ($itemsposts as $id) {
        if ($id == $idpost) continue;
        $same[$id] = isset($same[$id]) ? $same[$id] + 1 : 1;
      }
    }

    arsort($same);
    return array_slice(array_keys($same) , 0, $this->maxcount);
  }

  public function getsame($id) {
    $items = $this->db->getvalue($id, 'items');
    if (is_string($items)) {
      return $items == '' ? array() : explode(',', $items);
    } else {
      $result = $this->findsame($id);
      $this->db->add(array(
        'id' => $id,
        'items' => implode(',', $result)
      ));
      return $result;
    }
  }

  public function getcontent($id, $sidebar) {
    $post = $this->getcontext('tpost');
    $list = $this->getsame($post->id);
    if (count($list) == 0) return '';
    $posts = tposts::i();
    $posts->loaditems($list);
    $theme = ttheme::i();
    return $theme->getpostswidgetcontent($list, $sidebar, '');
  }

} //class