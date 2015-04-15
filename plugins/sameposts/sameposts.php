<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
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
      $this->revision += 1;
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
      $itemsposts= $posts->stripdrafts($itemsposts);
      foreach ($itemsposts as $id) {
        if ($id == $idpost) continue;
        $same[$id] = isset($same[$id]) ? $same[$id] + 1 : 1;
      }
    }
    
    arsort($same);
    return array_slice(array_keys($same), 0, $this->maxcount);
  }
  
  public function getsame($id) {
    if (dbversion) {
      $items = $this->db->getvalue($id, 'items');
      if (is_string($items)) {
        return $items == '' ? array() : explode(',', $items);
      } else {
        $result = $this->findsame($id);
        $this->db->add(array('id' => $id, 'items' => implode(',', $result)));
        return $result;
      }
    } else {
      $filename = litepublisher::$paths->data . 'posts' . DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR . 'same.php';
      $data = null;
      if (tfilestorage::loadvar($filename, $data)) {
        if ($data['revision'] == $this->revision) return $data['items'];
      }
      
      $result= $this->findsame($id);
      $data = array(
      'revision' => $this->revision,
      'items' => $result
      );
      tfilestorage::savevar($filename, $data);
      return $result;
    }
  }
  
  public function getcontent($id, $sidebar) {
    $post = $this->getcontext('tpost');
    $list = $this->getsame($post->id);
    if (count($list) == 0) return'';
    $posts = tposts::i();
    $posts->loaditems($list);
    $theme = ttheme::i();
    return $theme->getpostswidgetcontent($list, $sidebar, '');
  }
  
}//class