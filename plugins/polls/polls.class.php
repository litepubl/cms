<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpolls extends titems {
  public $votes;
  public $users1;
  public $users2;
  public $tml_items;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->addevents('edited');
    $this->basename = 'polls' . DIRECTORY_SEPARATOR . 'index';
    $this->table = 'polls';
    $this->votes = 'pollvotes';
    $this->users1 = 'pollusers1';
    $this->users2 = 'pollusers2';
    $this->tml_items = array();
    
    $this->data['autoid_tml'] = 0;
  }
  
  public function load() {
    return tfilestorage::load($this);
  }
  
  public function save() {
    if ($this->lockcount > 0) return;
    return tfilestorage::save($this);
  }
  
  public function add($id_tml, $status = 'opened') {
    return tpollsman::i()->add($id_tml, $status);
  }
  
  public function edit($id, $id_tml, $status) {
    return tpollsman::i()->edit($id, $id_tml, $status);
  }
  
  public function setstatus($id, $status) {
    $this->setvalue($id, 'status', $status);
  }
  
  public function delete($id) {
    $this->db->iddelete($id);
    $this->getdb($this->votes)->iddelete($id);
    $this->getdb($this->users1)->iddelete($id);
    $this->getdb($this->users2)->iddelete($id);
    $this->getdb('postsmeta')->delete("name = 'poll' and value = '$id'");
  }
  
  public function loadall_tml() {
    tpollsman::I()->loadall_tml();
  }
  
  public function getfilename($name) {
    return litepublisher::$paths->data . 'polls' . DIRECTORY_SEPARATOR . $name;
  }
  
  public function loadfile($name) {
    if (tfilestorage::loadvar($this->getfilename($name), $v)) return $v;
    return false;
  }
  
  public function get_tml($id_tml) {
    if (!isset($this->tml_items[$id_tml])) {
      $this->tml_items[$id_tml] = $this->loadfile($id_tml);
    }
    return $this->tml_items[$id_tml];
  }
  
  public function set_tml($id_tml, array $item) {
    $this->tml_items[$id_tml] = $item;
    tfilestorage::savevar($this->getfilename($id_tml), $item);
  }
  
  public function get_item_votes($id) {
    $result = array();
    $db = litepublisher::$db;
    $a = $db->res2assoc($db->query(sprintf('select * from %s%s where id = %d', $db->prefix, $this->votes, $id)));
    foreach ($a as $v) {
      $result[(int) $v['item']] = (int) $v['votes'];
    }
    return $result;
  }
  
  public function gethtml($id) {
    $item = $this->getitem($id);
    $tml = $this->get_tml($item['id_tml']);
    $args = new targs();
    $args->id = $id;
    $theme = ttheme::i();
    if ($item['status'] == 'opened') {
      //inject php into html
      $result = sprintf('<?php $poll = tpullpolls::i()->get(%d); ?>', $id);
      $args->total = '<?php echo $poll[\'total\']; ?>';
      $args->rate = '<?php echo $poll[\'rate\'] / 10; ?>';
      if (strpos($tml['closed'], '$votes')) {
        foreach ($tml['items'] as $index => $text) {
          $args->__set('votes' . $index, sprintf('<?php echo $poll[\'votes\'][%d]; ?>', $index));
        }
      }
      
      $result .= $theme->parsearg($tml['opened'] . $tml['closed'], $args);
    } else {
      $args->add($item);
      if (strpos($tml['closed'], '$votes')) {
        $votes = $this->get_item_votes($id);
        foreach ($votes as $i => $v) {
          $args->__set('votes' . $i, $v);
        }
      }
      
      $result = $theme->parsearg($tml['closed'], $args);
    }
    
    return $result;
  }
  
  public function add_tml($type, $name, $title, array $items) {
    $this->edit_tml(++$this->data['autoid_tml'], $type, $name, $title, $items);
    $this->save();
    return $this->data['autoid_tml'];
  }
  
  public function edit_tml($id_tml, $type, $name, $title, array $items) {
    $tml_item = tpolltypes::i()->build($type, $name, $title, $items);
    $this->set_tml($id_tml, $tml_item);
  }
  
  public function delete_tml($id_tml) {
    $this->db->update("id_tml = 1", "id_tml = $id_tml");
    $filename = $this->getfilename($id_tml);
    tfilestorage::delete($filename . '.php');
    tfilestorage::delete($filename . '.bak.php');
  }
  
  public function err($mesg) {
    $lang = tlocal::i('poll');
    
    return array(
    'code' => 'error',
    'message' => $lang->$mesg
    );
  }
  
  public function polls_sendvote(array $args) {
    extract($args, EXTR_SKIP);
    if (!isset($idpoll) || !isset($vote)) return $this->error('Invalid data', 403);
    $idpoll = (int) $idpoll;
    if ($idpoll == 0) return $this->error('Invalid data', 403);
    $vote = (int) $vote;
    $iduser = litepublisher::$options->user;
    if (!$iduser) return $this->err('notauth');
    if (!$this->itemexists($idpoll)) return $this->err('notfound');
    if ('closed' == $this->getvalue($idpoll, 'status')) return $this->err('closed');
    if ($this->hasvote($idpoll, $iduser)) return $this->err('voted');
    
    return $this->addvote($idpoll, $iduser, (int) $vote);
  }
  
  public function hasvote($idpoll, $iduser) {
    $q = sprintf('id = %d and user = %d', (int) $idpoll, (int) $iduser);
    //$this->getdb($this->users1)->delete($q);
    if ($this->getdb($this->users1)->findid($q)) return true;
    return $this->getdb($this->users2)->findid($q);
  }
  
  public function addvote($id, $iduser, $vote) {
    $result = array(
    'code' => 'success',
    'id' => $id,
    'total' => 0,
    'rate' => 0,
    'votes' => array()
    );
    
    $db = litepublisher::$db;
    $db->query(sprintf('INSERT INTO %s%s (id, user) values (%d,%d)', $db->prefix, $this->users1, $id, $iduser));
    $db->query(sprintf('update %s%s set votes = votes + 1 where id = %d and item = %d', $db->prefix, $this->votes, $id, (int) $vote));
    
    //update stat
    $a = $db->res2assoc($db->query(sprintf('select * from %s%s where id = %d', $db->prefix, $this->votes, $id)));
    $sum= 0;
    foreach ($a as $v) {
      $index = (int) $v['item'];
      $voted = (int) $v['votes'];
      $result['total'] += $voted;
      $result['votes'][$index] = $voted;
      $sum += ($index + 1) * $voted;
    }
    $result['rate'] = $result['total'] == 0 ? 0 : (int) round($sum / $result['total'] * 10);
    $this->db->updateassoc(array(
    'id' => $id,
    'rate' => $result['rate'],
    'total' => $result['total'],
    ));
    
    tpullpolls::i()->set($id, $result);
    $result['rate'] = (string) ($result['rate'] / 10);
    return $result;
  }
  
}//class