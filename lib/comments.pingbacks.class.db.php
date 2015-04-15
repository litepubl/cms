<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpingbacks extends tabstractpingbacks implements ipingbacks {
  
  public static function i($pid = 0) {
    $result = getinstance(__class__);
    $result->pid = $pid;
    return $result;
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->table = 'pingbacks';
    $this->basename = 'pingbacks';
  }
  
  public function doadd($url, $title) {
    $item = array(
    'url' => $url,
    'title' => $title,
    'post' => $this->pid,
    'posted' =>sqldate(),
    'status' => 'hold',
    'ip' => preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR'])
    );
    $id =     $this->db->add($item);
    $item['id'] = $id;
    $this->items[$id] = $item;
    $this->updatecount($this->pid);
    return $id;
  }
  
  private function updatecount($idpost) {
    $count= $this->db->getcount("post = $idpost and status = 'approved'");
    $this->getdb('posts')->setvalue($idpost, 'pingbackscount', $count);
  }
  
  public function edit($id, $title, $url) {
    $this->db->updateassoc(compact('id', 'title', 'url'));
  }
  
  public function exists($url) {
    return $this->db->finditem('url =' . dbquote($url));
  }
  
  public function setstatus($id, $approve) {
    $status = $approve ? 'approved' : 'hold';
    $item = $this->getitem($id);
    if ($item['status'] == $status) return false;
    $db = $this->db;
    $db->setvalue($id, 'status', $status);
    $this->updatecount($item['post']);
  }
  
  public function postdeleted($idpost) {
    $this->db->delete("post = $idpost");
  }
  
  public function import($url, $title, $posted, $ip, $status) {
    $item = array(
    'url' => $url,
    'title' => $title,
    'post' => $this->pid,
    'posted' =>sqldate($posted),
    'status' => $status,
    'ip' => $ip
    );
    $id =     $this->db->add($item);
    $item['id'] = $id;
    $this->items[$id] = $item;
    $this->updatecount($this->pid);
    return $id;
  }
  public function getcontent() {
    $result = '';
    $items = $this->db->getitems("post = $this->pid and status = 'approved' order by posted");
    $pingback = new tarray2prop();
    ttheme::$vars['pingback'] = $pingback;
    $lang = tlocal::i('comment');
    $theme = ttheme::i();
    $tml = $theme->content->post->templatecomments->pingbacks->pingback;
    foreach ($items as $item) {
      $pingback->array = $item;
      $result .= $theme->parse($tml);
    }
    return str_replace('$pingback', $result, $theme->parse($theme->content->post->templatecomments->pingbacks));
  }
  
}//class

?>