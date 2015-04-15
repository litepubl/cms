<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmenus extends titems {
  public $tree;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('edited', 'onprocessform', 'onbeforemenu', 'onmenu', 'onitems', 'onsubitems', 'oncontent');
    
    $this->dbversion = false;
    $this->basename = 'menus' . DIRECTORY_SEPARATOR   . 'index';
    $this->addmap('tree', array());
    $this->data['idhome'] = 0;
    $this->data['home'] = false;
  }
  
  public function getlink($id) {
    return sprintf('<a href="%1$s%2$s" title="%3$s">%3$s</a>', litepublisher::$site->url, $this->items[$id]['url'], $this->items[$id]['title']);
  }
  
  public function getdir() {
    return litepublisher::$paths->data . 'menus' . DIRECTORY_SEPARATOR;
  }
  
  public function add(tmenu $item) {
    if ($item instanceof tfakemenu) return $this->addfakemenu($item);
    //fix null fields
    foreach ($item->get_owner_props() as $prop) {
      if (!isset($item->data[$prop])) $item->data[$prop] = '';
    }
    
    if ($item instanceof thomepage) {
      $item->url = '/';
    } else {
      $linkgen = tlinkgenerator::i();
      $item->url = $linkgen->addurl($item, 'menu');
    }
    
    if ($item->idview == 1) {
      $views = tviews::i();
      if (isset($views->defaults['menu'])) $item->data['idview'] = $views->defaults['menu'];
    }
    
    $id = ++$this->autoid;
    $this->items[$id] = array(
    'id' => $id,
    'class' => get_class($item)
    );
    //move props
    foreach ($item->get_owner_props() as $prop) {
      if (array_key_exists($prop, $item->data)) {
        $this->items[$id][$prop] = $item->data[$prop];
        unset($item->data[$prop]);
      } else {
        $this->items[$id][$prop] = $item->$prop;
      }
    }
    
    $item->id = $id;
    $item->idurl = litepublisher::$urlmap->Add($item->url, get_class($item), $item->id);
    if ($item->status != 'draft') $item->status = 'published';
    $this->lock();
    $this->sort();
    $item->save();
    $this->unlock();
    $this->added($id);
    litepublisher::$urlmap->clearcache();
    return $id;
  }
  
  public function addfake($url, $title) {
    if ($id = $this->url2id($url)) return $id;
    
    $fake = new tfakemenu();
    $fake->title = $title;
    $fake->url = $url;
    $fake->order = $this->autoid;
    return $this->addfakemenu($fake);
  }
  
  public function addfakemenu(tmenu $menu) {
    $item = array(
    'id' => ++$this->autoid,
    'idurl' => 0,
    'class' => get_class($menu)
    );
    
    //fix null fields
    foreach ($menu->get_owner_props() as $prop) {
      if (!isset($menu->data[$prop])) $menu->data[$prop] = '';
      $item[$prop] = $menu->$prop;
      if (array_key_exists($prop, $menu->data)) unset($menu->data[$prop]);
    }
    
    $menu->id = $this->autoid;
    $this->items[$this->autoid] = $item;
    $this->lock();
    $this->sort();
    $this->added($this->autoid);
    $this->unlock();
    litepublisher::$urlmap->clearcache();
    return $this->autoid;
  }
  
  public function additem(array $item) {
    $item['id'] = ++$this->autoid;
    $item['order'] = $this->autoid;
    $item[    'status'] = 'published';
    
    if ($idurl = litepublisher::$urlmap->urlexists($item['url'])) {
      $item['idurl'] =  $idurl;
    } else {
      $item['idurl'] =litepublisher::$urlmap->add($item['url'], $item['class'], $this->autoid, 'get');
    }
    
    $this->items[$this->autoid] = $item;
    $this->sort();
    $this->save();
    litepublisher::$urlmap->clearcache();
    return $this->autoid;
  }
  
  public function edit(tmenu $item) {
    if (!( ($item instanceof thomepage) || ($item instanceof tfakemenu))) {
      $linkgen = tlinkgenerator::i();
      $linkgen->editurl($item, 'menu');
    }
    
    $this->lock();
    $this->sort();
    $item->save();
    $this->unlock();
    $this->edited($item->id);
    litepublisher::$urlmap->clearcache();
  }
  
  public function  delete($id) {
    if (!$this->itemexists($id)) return false;
    if($id == $this->idhome) return false;
    if ($this->haschilds($id)) return false;
    if ($this->items[$id]['idurl'] > 0) {
      litepublisher::$urlmap->delete($this->items[$id]['url']);
    }
    $this->lock();
    unset($this->items[$id]);
    $this->sort();
    $this->unlock();
    $this->deleted($id);
    tfilestorage::delete($this->dir . $id . '.php');
    tfilestorage::delete($this->dir . $id . '.bak.php');
    litepublisher::$urlmap->clearcache();
    return true;
  }
  
  public function deleteurl($url) {
    if ($id = $this->url2id($url)) return $this->delete($id);
  }
  
  public function deletetree($id) {
    if (!$this->itemexists($id)) return false;
    if($id == $this->idhome) return false;
    $this->lock();
    $childs = $this->getchilds($id);
    foreach ($childs as $child) {
      $this->deletetree($child);
    }
    $this->delete($id);
    $this->unlock();
  }
  
  public function url2id($url) {
    foreach ($this->items as $id => $item) {
      if ($url == $item['url']) return $id;
    }
    return false;
  }
  
  public function  remove($id) {
    if (!$this->itemexists($id)) return false;
    if ($this->haschilds($id)) return false;
    $this->lock();
    unset($this->items[$id]);
    $this->sort();
    $this->unlock();
    $this->deleted($id);
    litepublisher::$urlmap->clearcache();
    return true;
  }
  
  public function haschilds($idparent) {
    foreach ($this->items as $id => $item) {
      if ($item['parent'] == $idparent) return $id;
    }
    return false;
  }
  
  public function sort() {
    $this->tree = $this->getsubtree(0);
  }
  
  private function getsubtree($parent) {
    $result = array();
    // first step is a find all childs and sort them
    $sort= array();
    foreach ($this->items as $id => $item) {
      if (($item['parent'] == $parent) && ($item['status'] == 'published')) {
        $sort[$id] = (int) $item['order'];
      }
    }
    arsort($sort, SORT_NUMERIC);
    $sort = array_reverse($sort, true);
    
    foreach ($sort as $id => $order) {
      $result[$id]  = $this->getsubtree($id);
    }
    return $result;
  }
  
  
  public function getparent($id) {
    return $this->items[$id]['parent'];
  }
  
  //return array of id
  public function getparents($id) {
    $result = array();
    $id = $this->items[$id]['parent'];
    while ($id != 0) {
      //array_unshift ($result, $id);
      $result[] = $id;
      $id = $this->items[$id]['parent'];
    }
    return $result;
  }
  
  //ищет в дереве список детей, так как они уже отсортированы
  public function getchilds($id) {
    if ($id == 0) {
      $result = array();
      foreach ($this->tree as $iditem => $items) {
        $result[] = $iditem;
      }
      return $result;
    }
    
    $parents = array($id);
    $parent = $this->items[$id]['parent'];
    // fix of circle bug
    while ($parent && ($parent != $id)) {
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
    return !$this->home && ($id == $this->idhome);
  }
  
  public function getmenu($hover, $current) {
    $result = '';
    $this->callevent('onbeforemenu', array(&$result, &$hover,$current ));
    if (count($this->tree) > 0) {
      $theme = ttheme::i();
      $args = new targs();
      if ($hover) {
        $items = $this->getsubmenu($this->tree, $current, $hover === 'bootstrap');
      } else {
        $items = '';
        $tml = $theme->templates['menu.item'];
        $args->submenu = '';
        foreach ($this->tree as $id => $subitems) {
          if ($this->exclude($id)) continue;
          $args->add($this->items[$id]);
          $items .= $current == $id ? $theme->parsearg($theme->templates['menu.current'], $args) : $theme->parsearg($tml, $args);
        }
      }
      
      $this->callevent('onitems', array(&$items));
      $args->item =  $items;
      $result = $theme->parsearg($theme->templates['menu'], $args);
    }
    $this->callevent('onmenu', array(&$result));
    return $result;
  }
  
  private function getsubmenu(&$tree, $current, $bootstrap) {
    $result = '';
    $theme = ttheme::i();
    $tml_item = $theme->templates['menu.item'];
    $tml_submenu = $theme->templates['menu.item.submenu'];
    $tml_single = $theme->templates['menu.single'];
    $tml_current = $theme->templates['menu.current'];
    
    $args = new targs();
    foreach ($tree as $id => $items) {
      if ($this->exclude($id)) continue;
      $args->add($this->items[$id]);
      $submenu = '';
      if (count($items)) {
        if ($bootstrap) {
          $args->submenu = '';
          $submenu= $theme->parsearg($tml_single, $args);
        }
        $submenu .=  $this->getsubmenu($items, $current, $bootstrap);
        $submenu = str_replace('$items', $submenu, $tml_submenu);
      }
      
      $this->callevent('onsubitems', array($id, &$submenu));
      $args->submenu = $submenu;
      $tml = $current == $id ?  $tml_current : ($submenu ? $tml_item : $tml_single);
      $result .= $theme->parsearg($tml, $args);
    }
    
    return $result;
  }
  
  public function class2id($class) {
    foreach($this->items as $id => $item) {
      if ($class == $item['class']) return $id;
    }
    return false;
  }
  
  public function getsitemap($from, $count) {
    return $this->externalfunc(__class__, 'Getsitemap', array($from, $count));
  }
  
}//class

class tmenu extends titem implements  itemplate {
  public static $ownerprops = array('title', 'url', 'idurl', 'parent', 'order', 'status');
  public $formresult;
  
  public static function i($id = 0) {
    $class = $id == 0 ? __class__ : self::getowner()->items[$id]['class'];
    return self::iteminstance($class,  $id);
  }
  
  public static function iteminstance($class, $id = 0) {
    $single = getinstance($class);
    if ($single->id == $id) return $single;
    if (($id == 0) && ($single->id > 0)) return $single;
    if (($single->id == 0) && ($id > 0)) return $single->loaddata($id);
    return parent::iteminstance($class, $id);
  }
  
  public static function singleinstance($class) {
    $single = getinstance($class);
    if ($id = $single->get_owner()->class2id($class)) {
      if ($single->id == $id) return $single;
      if (($single->id == 0) && ($id > 0)) return $single->loaddata($id);
    }
    return $single;
  }
  
  public static function getinstancename() {
    return 'menu';
  }
  
  public static function getowner() {
    return tmenus::i();
  }
  
  public function get_owner() {
    return call_user_func_array(array(get_class($this), 'getowner'), array());
  }
  
  protected function create() {
    parent::create();
    $this->formresult = '';
    $this->data= array(
    'id' => 0,
    'author' => 0, //not supported
    'content' => '',
    'rawcontent' => '',
    'keywords' => '',
    'description' => '',
    'head' => '',
    'password' => '',
    'idview' => 1,
    //owner props
    'title' => '',
    'url' => '',
    'idurl' => 0,
    'parent' => 0,
    'order' => 0,
    'status' => 'published'
    );
  }
  
  public function getbasename() {
    return 'menus' . DIRECTORY_SEPARATOR . $this->id;
  }
  
  public function __get($name) {
    if ($name == 'content') return $this->formresult . $this->getcontent();
    if ($name == 'id') return $this->data['id'];
    if (method_exists($this, $get = 'get' . $name))  return $this->$get();
    
    if ($this->is_owner_prop($name)) return $this->getownerprop($name);
    return parent::__get($name);
  }
  
  public function get_owner_props() {
    return self::$ownerprops;
  }
  
  public function is_owner_prop($name) {
    return in_array($name, $this->get_owner_props());
  }
  
  public function getownerprop($name) {
    $id = $this->data['id'];
    if ($id == 0) {
      return $this->data[$name];
    } else if (isset($this->getowner()->items[$id])) {
      return $this->getowner()->items[$id][$name];
    } else {
      $this->error(sprintf('%s property not found in %d items', $id, $name));
    }
  }
  
  public function __set($name, $value) {
    if ($this->is_owner_prop($name)) {
      if ($this->id == 0) {
        $this->data[$name] = $value;
      } else {
        $this->owner->setvalue($this->id, $name, $value);
      }
      return;
    }
    parent::__set($name, $value);
  }
  
  public function __isset($name) {
    if ($this->is_owner_prop($name)) return true;
    return parent::__isset($name);
  }
  
  //ITemplate
  public function request($id) {
    parent::request($id);
    if ($this->status == 'draft') return 404;
    $this->doprocessform();
  }
  
  protected function doprocessform() {
    if (tguard::post()) {
      $this->formresult.= $this->processform();
    }
  }
  
  public function processform() {
    return $this->owner->onprocessform($this->id);
  }
  
  public function gethead() {
    return $this->data['head'];
  }
  
  public function gettitle() {
    return $this->getownerprop('title');
  }
  
  public function getkeywords() {
    return $this->data['keywords'];
  }
  
  public function getdescription() {
    return $this->data['description'];
  }
  
  public function getidview() {
    return $this->data['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->idview) {
      $this->data['idview'] = $id;
      $this->save();
    }
  }
  
  public function getcont() {
    return ttheme::parsevar('menu', $this, ttheme::i()->templates['content.menu']);
  }
  
  public function getlink() {
    return litepublisher::$site->url . $this->url;
  }
  
  public function getcontent() {
    $result = $this->data['content'];
    $this->owner->callevent('oncontent', array($this, &$result));
    return $result;
  }
  
  public function setcontent($s) {
    if (!is_string($s)) $this->error('Error! Page content must be string');
    if ($s != $this->rawcontent) {
      $this->rawcontent = $s;
      $filter = tcontentfilter::i();
      $this->data['content'] = $filter->filter($s);
    }
  }
  
}//class

class tfakemenu extends tmenu {
  
  public static function i($id = 0) {
    return self::iteminstance(__class__,  $id);
  }
  
  public function load() {
    return true;
  }
  
public function save() {}
}//class

class tsinglemenu extends tmenu {
  
  public function __construct() {
    parent::__construct();
    if ($id = $this->getowner()->class2id(get_class($this))) {
      $this->loaddata($id);
    }
  }
  
}//class