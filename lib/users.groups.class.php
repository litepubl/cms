<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tusergroups extends titems {
  public $defaults;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'usergroups';
    $this->addmap('defaults', array());
    $this->data['defaulthome'] = '/admin/';
  }
  
  public function add($name, $title, $home) {
    if ($id = $this->getidgroup($name)) return $id;
    $this->items[++$this->autoid] = array(
    'name' => $name,
    'title' => $title,
    'home' => $home,
    'parents' => array()
    );
    $this->save();
    return $this->autoid;
  }
  
  public function delete($id) {
    if (!isset($this->items[$id])) return false;
    unset($this->items[$id]);
    $this->save();
    
    $users = tusers::i();
    $db = $users->db;
    $items = $db->res2assoc($users->getdb($users->grouptable)->select("idgroup = $id"));
    $users->getdb($users->grouptable)->delete("idgroup = $id");
    foreach ($items as $item) {
      $iduser = $item['iduser'];
      $idgroups = $db->res2id($db->query("select idgroup from $db->prefix$users->grouptable where iduser = $iduser"));
      $users->db->setvalue($iduser, 'idgroups', implode(',', $idgroups));
    }
  }
  
  public function save() {
    parent::save();
    $this->update();
  }
  
  public function update() {
    litepublisher::$options->data['groupnames'] = array();
    $groupnames = &litepublisher::$options->data['groupnames'];
    litepublisher::$options->data['parentgroups'] = array();
    $parentgroups = &litepublisher::$options->data['parentgroups'];
    
    foreach ($this->items as $id => $group) {
      $names = explode(',', $group['name']);
      foreach ($names as $name) {
        if ($name = trim($name)) $groupnames[$name] = $id;
      }
      $parentgroups[$id] = $group['parents'];
    }
    litepublisher::$options->save();
  }
  
  public function getidgroup($name) {
    $name = trim($name);
    return isset(litepublisher::$options->groupnames[$name]) ? litepublisher::$options->groupnames[$name] : false;
  }
  
  public function addparent($id, $name) {
    if (!isset($this->items[$id])) return false;
    if ($idparent = $this->getidgroup($name)) {
      if (in_array($idparent, $this->items[$id]['parents'])) return false;
      $this->items[$id]['parents'][] = $idparent;
      $this->save();
      return $idparent;
    }
  }
  
  public function addchild($id, $name) {
    if (!isset($this->items[$id])) return false;
    if ($idchild = $this->getidgroup($name)) {
      if (in_array($id, $this->items[$idchild]['parents'])) return false;
      $this->items[$idchild]['parents'][] = $id;
      $this->save();
      return $idchild;
    }
  }
  
  
  public function gethome($name) {
    if ($id = $this->getidgroup($name)) {
      return isset($this->items[$id]['home']) ? $this->items[$id]['home'] : $this->defaulthome;
    }
    return $this->defaulthome;
  }
  
}//class