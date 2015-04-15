<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpollsman extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'polls' . DIRECTORY_SEPARATOR . 'man';
    $this->data['addtopost'] = false;
    $this->data['pollpost'] = 0;
    $this->data['fivestars'] = 0;
    //in hours
    $this->data['lifetime'] = 24 * 7;
    $this->data['lastswitched'] = 0;
  }
  
  public function add($id_tml, $status) {
    $id_tml = (int) $id_tml;
    if ($id_tml == 0) $id_tml = $this->pollpost;
    $polls = tpolls::i();
    if (!($tml = $polls->get_tml($id_tml))) $this->error(sprintf('The "%d" poll template not found', $id_tml));
    if (($status != 'opened') && ($status != 'closed')) $this->error(sprintf('Unknown status "%s"', $status));
    
    $item = array(
    'id_tml' => $id_tml,
    'status' => $status,
    'total' => 0,
    'rate' => 0
    );
    $id = $polls->db->add($item);
    $polls->items[$id] = $item;
    $db = $polls->getdb($polls->votes);
    foreach ($tml['items'] as $i => $text) {
      $db->insertrow("(id, item, votes) values ('$id', '$i', '0')");
    }
    $polls->added($id);
    return $id;
  }
  
  public function edit($id, $id_tml, $status) {
    $polls = tpolls::i();
    if (!$polls->itemexists($id)) $this->error(sprintf('Item "%d" not found', $id));
    if (($status != 'opened') && ($status != 'closed')) $this->error(sprintf('Unknown status "%s"', $status));
    $item = $polls->getitem($id);
    $item['status'] = $status;
    if ($id_tml != $item['id_tml']) {
      if (!($tml = $polls->get_tml($id_tml))) $this->error(sprintf('The "%d" poll template not found', $id_tml));
      $votes = $polls->get_item_votes($id);
      $db = $polls->getdb($polls->votes);
      $db->iddelete($id);
      foreach ($tml['items'] as $i => $text) {
        $v = isset($votes[$i]) ? $votes[$i] : 0;
        $db->insertrow("(id, item, votes) values ($id,$i,$v)");
      }
    }
    
    $polls->items[$id] = $item;
    $polls->db->updateassoc($item);
    $polls->edited($id);
    return true;
  }
  
  public function loadall_tml() {
    $polls = tpolls::i();
    $filelist = tfiler::getfiles(litepublisher::$paths->data . 'polls');
    foreach($filelist as $filename) {
      if (preg_match('/^(\d*+)\.php$/', $filename, $m)) {
        $id = (int) $m[1];
        $tml = $polls->get_tml($id);
      }
    }
    ksort ($polls->tml_items, SORT_NUMERIC);
  }
  
  public function optimize() {
    //delete deleted users
    if(($this->lastswitched + ($this->lifetime * 3600)) > time()) {
      $this->switchtables();
      $this->lastswitched = time();
      $this->save();
    }
  }
  
  public function switchtables() {
    $man = tdbmanager::i();
    $polls = tpolls::i();
    $users1 = $man->prefix . $polls->users1;
    $users2 = $man->prefix . $polls->users2;
    $res = dirname(__file__) .DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    $man->deletetable($polls->users2);
    $man->query("rename table $users1 to $users2");
    $man->createtable($polls->users1, file_get_contents($res . 'users.sql'));
  }
  
  public function postadded($idpost) {
    if ($this->pollpost == 0) return;
    $post = tpost::i($idpost);
    $post->meta->poll = $this->add($this->pollpost, 'opened');
  }
  
  public function afterpost(tpost $post, &$content) {
    if (isset($post->meta->poll)) {
      $content = tpolls::i()->gethtml($post->meta->poll) . $content;
    }
  }
  
  public function postdeleted($id) {
    if (!dbversion) return;
    $meta = tmetapost::i($id);
    if (isset($meta->poll)) {
      $this->delete($meta->poll);
    }
  }
  
  public function filter(&$content) {
    if (preg_match_all('/\[poll\=(\d*?)\]/', $content, $m, PREG_SET_ORDER)) {
      $polls = tpolls::i();
      foreach ($m as $item) {
        $id = (int) $item[1];
        if ($polls->itemexists($id)) {
          $html = $polls->gethtml($id);
          $html = '[html]' . $html . '[/html]';
        } else {
          $html = '';
        }
        
        $content = str_replace($item[0], $html, $content);
      }
    }
  }
  
}//class