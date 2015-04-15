<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tperm extends titem_storage {
  protected $_admin;
  protected $adminclass;
  
  public static function i($id = 0) {
    $perms = tperms::i();
    $class = $perms->itemexists($id) ? $perms->items[$id]['class'] : __class__;
    return parent::iteminstance($class, $id);
  }
  
  public static function getinstancename() {
    return 'perm';
  }
  
  protected function create() {
    parent::create();
    $this->data = array(
    'id' => 0,
    'class' => get_class($this),
    'name' => 'permission'
    );
  }
  
  public function getowner() {
    return tperms::i();
  }
  
  public function getadmin() {
    if (!isset($this->_admin)) {
      $class = $this->adminclass;
      $this->_admin = litepublisher::$classes->newinstance($class);
      $this->_admin->perm = $this;
    }
    return$this->_admin;
  }
  
  public function getheader($obj) {
    return '';
  }
  
  public function hasperm($obj) {
    return true;
  }
  
}//class

class tpermgroups extends tperm {
  
  protected function create() {
    parent::create();
    $this->adminclass = 'tadminpermgroups';
    $this->data['author'] = false;
    $this->data['groups'] = array();
  }
  
  public function getheader($obj) {
    $g = $this->groups;
    if (!$this->author  && (count($g) == 0)) return '';
    $author = '';
    if ($this->author && isset($obj->author) && ($obj->author > 1)) {
      $author = sprintf('  || (litepublisher::$options->user != %d)', $obj->author);
    }
    
    return sprintf('<?php if (!litepublisher::$options->ingroups( array(%s)) %s) return litepublisher::$urlmap->forbidden(); ?>',  implode(',', $g), $author);
  }
  
  public function hasperm($obj) {
    $g = $this->groups;
    if (!$this->author  && (count($g) == 0)) return true;
    if (litepublisher::$options->ingroups($g)) return true;
    return $this->author && isset($obj->author) && ($obj->author > 1) && (litepublisher::$options->user == $obj->author);
  }
  
}//class

class tperms extends titems_storage {
  public $classes;
  public $tables;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'perms';
    $this->addmap('classes', array());
    $this->tables = array('files', 'posts', 'tags', 'categories');
  }
  
  public function addclass(tperm $perm) {
    $this->classes[get_class($perm)] = $perm->name;
    $this->save();
  }
  
  public function add(tperm $perm) {
    $this->lock();
    $id = ++$this->autoid;
    $perm->id = $id;
    $perm->data['class'] = get_class($perm);
    if ($perm->name == 'permission') $perm->name .= $id;
    $this->items[$id] = &$perm->data;
    $this->unlock();
    return $id;
  }
  
  public function delete($id) {
    if ($id == 1) return false;
    if (!isset($this->items[$id])) return false;
    if (dbversion) {
      $db = litepublisher::$db;
      foreach ($this->tables as $table) {
        $db->table = $table;
        $db->update('idperm = 0', "idperm = $id");
      }
    }
    return parent::delete($id);
  }
  
}//class