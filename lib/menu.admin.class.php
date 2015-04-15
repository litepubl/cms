<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminmenus extends tmenus {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'adminmenu';
    $this->addevents('onexclude');
    $this->data['heads'] = '';
  }
  
  public function settitle($id, $title) {
    if ($id && isset($this->items[$id])) {
      $this->items[$id]['title'] = $title;
      $this->save();
      litepublisher::$urlmap->clearcache();
    }
  }
  
  public function getdir() {
    return litepublisher::$paths->data . 'adminmenus' . DIRECTORY_SEPARATOR;
  }
  
  public function getadmintitle($name) {
    $lang = tlocal::i();
    $ini = &$lang->ini;
    if (isset($ini[$name]['title'])) return $ini[$name]['title'];
    tlocal::usefile('install');
    if (!in_array('adminmenus', $lang->searchsect)) array_unshift($lang->searchsect, 'adminmenus');
    if ($result = $lang->__get($name)) return $result;
    return $name;
  }
  
  public function createurl($parent, $name) {
    return $parent == 0 ? "/admin/$name/" : $this->items[$parent]['url'] . "$name/";
  }
  
  public function createitem($parent, $name, $group, $class) {
    $title = $this->getadmintitle($name);
    $url = $this->createurl($parent, $name);
    return $this->additem(array(
    'parent' => $parent,
    'url' => $url,
    'title' => $title,
    'name' => $name,
    'class' => $class,
    'group' => $group
    ));
  }
  
  public function additem(array $item) {
    if (empty($item['group'])) {
      $groups = tusergroups::i();
      $item['group'] = $groups->items[$groups->defaults[0]]['name'];
    }
    return parent::additem($item);
  }
  
  public function addfakemenu(tmenu $menu) {
    $this->lock();
    $id = parent::addfakemenu($menu);
    if (empty($this->items[$id]['group'])) {
      $groups = tusergroups::i();
      $group = count($groups->defaults)  ? $groups->items[$groups->defaults[0]]['name'] : 'commentator';
      $this->items[$id]['group'] = $group;
    }
    
    $this->unlock();
    return $id;
  }
  
  public function getchilds($id) {
    if ($id == 0) {
      $result = array();
      $options = litepublisher::$options;
      foreach ($this->tree as $iditem => $items) {
        if ($options->hasgroup($this->items[$iditem]['group']))
        $result[] = $iditem;
      }
      return $result;
    }
    
    $parents = array($id);
    $parent = $this->items[$id]['parent'];
    while ($parent != 0) {
      array_unshift ($parents, $parent);
      $parent = $this->items[$parent]['parent'];
    }
    
    $tree = $this->tree;
    foreach ($parents as $parent) {
      foreach ($tree as $iditem => $items) {
        if ($iditem == $parent) {
          $tree = $items;
          break;
        }
      }
    }
    return array_keys($tree);
  }
  
  public function exclude($id) {
    if (!litepublisher::$options->hasgroup($this->items[$id]['group'])) return  true;
    return $this->onexclude($id);
  }
  
}//class

class tadminmenu  extends tmenu {
  public static $adminownerprops = array('title', 'url', 'idurl', 'parent', 'order', 'status', 'name', 'group');
  public $arg;
  
  public static function getinstancename() {
    return 'adminmenu';
  }
  
  public static function getowner() {
    return tadminmenus::i();
  }
  
  protected function create() {
    parent::create();
    $this->cache = false;
  }
  
  public function get_owner_props() {
    return self::$adminownerprops;
  }
  
public function load() { return true; }
public function save() { return true; }
  
  public function gethead() {
    return tadminmenus::i()->heads;
  }
  
  public function getidview() {
    return tviews::i()->defaults['admin'];
  }
  
  public static function auth($group) {
    if ($s = tguard::checkattack()) return $s;
    if (!litepublisher::$options->user) {
      turlmap::nocache();
      return litepublisher::$urlmap->redir('/admin/login/' . litepublisher::$site->q . 'backurl=' . urlencode(litepublisher::$urlmap->url));
    }
    
    if (!litepublisher::$options->hasgroup($group)) {
      $url = tusergroups::i()->gethome(litepublisher::$options->group);
      turlmap::nocache();
      return litepublisher::$urlmap->redir($url);
    }
  }
  
  public function request($id) {
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);
    
    if (is_null($id)) $id = $this->owner->class2id(get_class($this));
    $this->data['id'] = (int)$id;
    if ($id > 0) {
      $this->basename =  $this->parent == 0 ? $this->name : $this->owner->items[$this->parent]['name'];
    }
    
    if ($s = self::auth($this->group)) return $s;
    tlocal::usefile('admin');
    $this->arg = litepublisher::$urlmap->argtree;
    if ($s = $this->canrequest()) return $s;
    $this->doprocessform();
  }
  
public function canrequest() { }
  
  protected function doprocessform() {
    if (tguard::post()) {
      litepublisher::$urlmap->clearcache();
    }
    return parent::doprocessform();
  }
  
  public function getcont() {
    if (litepublisher::$options->admincache) {
      $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
      $filename = 'adminmenu.' . litepublisher::$options->user . '.' .md5($_SERVER['REQUEST_URI'] . '&id=' . $id) . '.php';
      if ($result = litepublisher::$urlmap->cache->get($filename)) return $result;
      $result = parent::getcont();
      litepublisher::$urlmap->cache->set($filename, $result);
      return $result;
    } else {
      return parent::getcont();
    }
  }
  
  public static function idget() {
    return (int) tadminhtml::getparam('id', 0);
  }
  
  public function getaction() {
    return isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
  }
  
  public function gethtml($name = '') {
    $result = tadminhtml::i();
    if ($name == '') $name = $this->basename;
    if (!isset($result->ini[$name]) && $this->parent) {
      $name = $this->owner->items[$this->parent]['name'];
    }
    
    $result->section = $name;
    $lang = tlocal::i($name);
    return $result;
  }
  
  public function getlang() {
    return tlocal::i($this->name);
  }
  
  public function getadminlang() {
    return tlocal::inifile($this, '.admin.ini');
  }
  
  public function inihtml($name = '') {
    $html = $this->gethtml($name);
    $html->iniplugin(get_class($this));
    return $html;
  }
  
  public function getconfirmed() {
    return isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1);
  }
  
  public function getnotfound() {
    return $this->html->h4red->notfound;
  }
  
  public function getadminurl() {
    return litepublisher::$site->url .$this->url . litepublisher::$site->q . 'id';
  }
  
  public function getfrom($perpage, $count) {
    if (litepublisher::$urlmap->page <= 1) return 0;
    return min($count, (litepublisher::$urlmap->page - 1) * $perpage);
  }
  
}//class

class tauthor_rights extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('gethead', 'getposteditor', 'editpost', 'changeposts', 'canupload', 'candeletefile');
    $this->basename = 'authorrights';
  }
  
}//class