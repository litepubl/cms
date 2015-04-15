<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tusers extends titems {
  public $grouptable;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->basename = 'users';
    $this->table = 'users';
    $this->grouptable = 'usergroup';
    $this->addevents('beforedelete');
  }
  
  public function res2items($res) {
    if (!$res) return array();
    $result = array();
    $db = litepublisher::$db;
    while ($item = $db->fetchassoc($res)) {
      $id = (int) $item['id'];
      $item['idgroups'] = tdatabase::str2array($item['idgroups']);
      $result[] = $id;
      $this->items[$id] = $item;
    }
    return $result;
  }
  
  public function add(array $values) {
    return tusersman::i()->add($values);
  }
  
  public function edit($id, array $values) {
    return tusersman::i()->edit($id, $values);
  }
  
  public function setgroups($id, array $idgroups) {
    $idgroups = array_unique($idgroups);
    array_delete_value($idgroups, '');
    array_delete_value($idgroups, false);
    array_delete_value($idgroups, null);
    
    $this->items[$id]['idgroups'] = $idgroups;
    $db = $this->getdb($this->grouptable);
    $db->delete("iduser = $id");
    foreach ($idgroups as $idgroup) {
      $db->add(array(
      'iduser' => $id,
      'idgroup' => $idgroup
      ));
    }
  }
  
  public function delete($id) {
    if ($id == 1) return;
    $this->beforedelete($id);
    $this->getdb($this->grouptable)->delete('iduser = ' .(int)$id);
    tuserpages::i()->delete($id);
    $this->getdb('comments')->update("status = 'deleted'", "author = $id");
    return parent::delete($id);
  }
  
  public function emailexists($email) {
    if ($email == '') return false;
    if ($email == litepublisher::$options->email) return 1;
    
    foreach ($this->items as $id => $item) {
      if ($email == $item['email']) return $id;
    }
    
    if ($item = $this->db->finditem('email = '. dbquote($email))) {
      $id = (int) $item['id'];
      $this->items[$id] = $item;
      return $id;
    }
    
    return false;
  }
  
  public function getpassword($id) {
    return $id == 1 ? litepublisher::$options->password : $this->getvalue($id, 'password');
  }
  
  public function changepassword($id, $password) {
    $item = $this->getitem($id);
    $this->setvalue($id, 'password', litepublisher::$options->hash($item['email'] . $password));
  }
  
  public function approve($id) {
    $this->setvalue($id, 'status', 'approved');
    $pages = tuserpages::i();
    if ($pages->createpage) $pages->addpage($id);
  }
  
  public function auth($email,$password) {
    return $this->authpassword($this->emailexists($email), $password);
  }
  
  public function authpassword($id,$password) {
    if (!$id || !$password) return false;
    $item = $this->getitem($id);
    if ($item['password'] == litepublisher::$options->hash($item['email']. $password)) {
      if ($item['status'] == 'wait') $this->approve($id);
      return $id;
    }
    return false;
  }
  
  public function authcookie($cookie) {
    $cookie = (string) $cookie;
    if (empty($cookie)) return false;
    $cookie = litepublisher::$options->hash( $cookie);
    if ($cookie == litepublisher::$options->hash('')) return false;
    if ($id = $this->findcookie($cookie)) {
      $item = $this->getitem($id);
      if (strtotime($item['expired']) > time()) return  $id;
    }
    return false;
  }
  
  public function findcookie($cookie) {
    $cookie = dbquote($cookie);
    if (($a = $this->select('cookie = ' . $cookie, 'limit 1')) && (count($a) > 0)) {
      return (int) $a[0];
    }
    return false;
  }
  
  public function getgroupname($id) {
    $item = $this->getitem($id);
    $groups = tusergroups::i();
    return $groups->items[$item['idgroups'][0]]['name'];
  }
  
  public function clearcookie($id) {
    $this->setcookie($id, '', 0);
  }
  
  public function setcookie($id, $cookie, $expired) {
    if ($cookie) $cookie = litepublisher::$options->hash($cookie);
    $expired = sqldate($expired);
    if (isset($this->items[$id])) {
      $this->items[$id]['cookie'] = $cookie;
      $this->items[$id]['expired'] = $expired;
    }
    
    $this->db->updateassoc(array(
    'id' => $id,
    'cookie' => $cookie,
    'expired' => $expired
    ));
  }
  
}//class