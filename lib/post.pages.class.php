<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpostpages extends tbasepostprops {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'pages';
    $this->dataname = 'pages';
  }
  
  public function add(tpost $post) {
    $db = $this->db;
    foreach ($post->syncdata[$this->dataname] as $page => $content) {
      $db->insert(array(
      'id' => $post->id,
      'page' => $page,
      'content' => $content
      ));
    }
  }
  
  public function save(tpost $post) {
    $this->clear();
    $this->add($post);
  }
  
  public function addpage(tpost $post, $s) {
    $post->syncdata[$this->dataname][] = $s;
    $post->pagescount = count($post->syncdata[$this->dataname]);
    if ($post->id > 0) {
      $this->db->insert(array(
      'id' => $post->id,
      'page' => $post->pagescount -1,
      'content' => $s
      ));
    }
  }
  
  public function clearpages(tpost $post) {
    $post->syncdata[$this->dataname] = array();
    $post->pagescount = 0;
    if ($post->id > 0) $this->db->iddelete($post->id);
  }
  
  public function getpage(tpost $post, $i) {
    if (!isset($post->propdata[$this->dataname])  $post->propdata[$this->dataname] = array();
    $data = &$post->propdata[$this->dataname];
    if ( isset($data[$i]))   return $data[$i];
    if ($post->id > 0) {
      if ($r = $this->db->getassoc("(id = $post->id) and (page = $i) limit 1")) {
        $data[$i] = $r['content'];
      } else {
        $data[$i]= false;
      }
      return $data[$i];
    }
    return false;
  }
  
}//class