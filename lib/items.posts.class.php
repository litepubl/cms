<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class titemsposts extends titems {
  public $tablepost;
  public $postprop;
  public $itemprop;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'itemsposts';
    $this->table = 'itemsposts';
    $this->tablepost = 'posts';
    $this->postprop = 'post';
    $this->itemprop = 'item';
  }
  
  public function add($idpost, $iditem) {
    $this->db->insert(array(
    $this->postprop => $idpost,
    $this->itemprop => $iditem
    ));
    $this->added();
  }
  
  public function exists($idpost, $iditem) {
    return $this->db->exists("$this->postprop = $idpost and $this->itemprop = $iditem");
  }
  
  public function remove($idpost, $iditem) {
    return $this->db->delete("$this->postprop = $idpost and $this->itemprop = $iditem");
  }
  
  public function delete($idpost) {
    return $this->deletepost($idpost);
  }
  
  public function deletepost($idpost) {
    $db = $this->db;
    $result = $db->res2id($db->query("select $this->itemprop from $this->thistable where $this->postprop = $idpost"));
    $db->delete("$this->postprop = $idpost");
    return $result;
  }
  
  public function deleteitem($iditem) {
    $this->db->delete("$this->itemprop = $iditem");
    $this->deleted();
  }
  
  public function setitems($idpost, array $items) {
    array_clean($items);
    $db = $this->db;
    $old = $this->getitems($idpost);
    $add = array_diff($items, $old);
    $delete = array_diff($old, $items);
    
    if (count($delete)) $db->delete("$this->postprop = $idpost and $this->itemprop in (" . implode(', ', $delete) . ')');
    if (count($add)) {
      $vals = array();
      foreach ($add as $iditem) {
        $vals[]= "($idpost, $iditem)";
      }
      $db->exec("INSERT INTO $this->thistable ($this->postprop, $this->itemprop) values " . implode(',', $vals) );
    }
    
    return array_merge($old, $add);
  }
  
  public function getitems($idpost) {
    return litepublisher::$db->res2id(litepublisher::$db->query("select $this->itemprop from $this->thistable where $this->postprop = $idpost"));
  }
  
  public function getposts($iditem) {
    return litepublisher::$db->res2id(litepublisher::$db->query("select $this->postprop from $this->thistable where $this->itemprop = $iditem"));
  }
  
  public function getpostscount($ititem) {
    $db = $this->getdb($this->tablepost);
    return $db->getcount("$db->prefix$this->tablepost.status = 'published' and id in (select $this->postprop from $this->thistable where $this->itemprop = $ititem)");
  }
  
  public function updateposts(array $list, $propname) {
    $db = $this->db;
    foreach ($list as $idpost) {
      $items = $this->getitems($idpost);
      $db->table = $this->tablepost;
      $db->setvalue($idpost, $propname, implode(', ', $items));
    }
  }
  
}//class

class titemspostsowner extends titemsposts {
  private $owner;
  public function __construct($owner) {
    if (!isset($owner)) return;
    parent::__construct();
    $this->owner = $owner;
    $this->table = $owner->table . 'items';
  }
  
public function load() { }
public function save() { $this->owner->save(); }
public function lock() { $this->owner->lock(); }
public function unlock() { $this->owner->unlock(); }
  
}//class