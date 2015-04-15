<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tview extends titem_storage {
  public $sidebars;
  protected $themeinstance;
  
  public static function i($id = 1) {
    if ($id == 1) {
      $class = __class__;
    } else {
      $views = tviews::i();
      $class = $views->itemexists($id) ? $views->items[$id]['class'] : __class__;
    }
    return parent::iteminstance($class, $id);
  }
  
  public static function getinstancename() {
    return 'view';
  }
  
  public static function getview($instance) {
    $id = $instance->getidview();
    if (isset(self::$instances['view'][$id]))     return self::$instances['view'][$id];
    $views = tviews::i();
    if (!$views->itemexists($id)) {
      $id = 1; //default, wich always exists
      $instance->setidview($id);
    }
    return self::i($id);
  }
  
  protected function create() {
    parent::create();
    $this->data = array(
    'id' => 0,
    'class' => get_class($this),
    'name' => 'default',
    'themename' => 'default',
    'menuclass' => 'tmenus',
    'hovermenu' => true,
    'customsidebar' => false,
    'disableajax' => false,
    'custom' => array(),
    'sidebars' => array()
    );
    $this->sidebars = &$this->data['sidebars'];
    $this->themeinstance = null;
  }
  
  public function __destruct() {
    unset($this->themeinstance);
    parent::__destruct();
  }
  
  public function getowner() {
    return tviews::i() ;
  }
  
  public function load() {
    if (parent::load()) {
      $this->sidebars = &$this->data['sidebars'];
      return true;
    }
    return false;
  }
  
  protected function get_theme_instance($name) {
    return ttheme::getinstance($name);
  }
  
  public function setthemename($name) {
    if ($name != $this->themename) {
      if (!ttheme::exists($name)) return $this->error(sprintf('Theme %s not exists', $name));
      $this->data['themename'] = $name;
      $this->themeinstance = $this->get_theme_instance($name);
      $this->data['custom'] = $this->themeinstance->templates['custom'];
      $this->save();
      tviews::i()->themechanged($this);
    }
  }
  
  public function gettheme() {
    if (isset($this->themeinstance)) return $this->themeinstance;
    if (ttheme::exists($this->themename)) {
      $this->themeinstance = $this->get_theme_instance($this->themename);
      $viewcustom = &$this->data['custom'];
      $themecustom = &$this->themeinstance->templates['custom'];
      //aray_equal
      if ((count($viewcustom) == count($themecustom)) && !count(array_diff(array_keys($viewcustom), array_keys($themecustom)))) {
        $this->themeinstance->templates['custom'] = $viewcustom;
      } else {
        $this->data['custom'] = $themecustom;
        $this->save();
      }
    } else {
      $this->setthemename('default');
    }
    return $this->themeinstance;
  }
  
  public function setcustomsidebar($value) {
    if ($value != $this->customsidebar) {
      if ($this->id == 1) return false;
      if ($value) {
        $default = tview::i(1);
        $this->sidebars = $default->sidebars;
      } else {
        $this->sidebars = array();
      }
      $this->data['customsidebar'] = $value;
      $this->save();
    }
  }
  
}//class

class tviews extends titems_storage {
  public $defaults;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'views';
    $this->addevents('themechanged');
    $this->addmap('defaults', array());
  }
  
  public function add($name) {
    $this->lock();
    $id = ++$this->autoid;
    $view = litepublisher::$classes->newitem(tview::getinstancename(), 'tview', $id);
    $view->id = $id;
    $view->name = $name;
    $view->data['class'] = get_class($view);
    $this->items[$id] = &$view->data;
    $this->unlock();
    return $id;
  }
  
  public function addview(tview $view) {
    $this->lock();
    $id = ++$this->autoid;
    $view->id = $id;
    if ($view->name == '') $view->name = 'view_' . $id;
    $view->data['class'] = get_class($view);
    $this->items[$id] = &$view->data;
    $this->unlock();
    return $id;
  }
  
  public function delete($id) {
    if ($id == 1) return $this->error('You cant delete default view');
    foreach ($this->defaults as $name => $iddefault) {
      if ($id == $iddefault) $this->defaults[$name] = 1;
    }
    return parent::delete($id);
  }
  
  public function get($name) {
    foreach ($this->items as $id => $item) {
      if ($name == $item['name']) return tview::i($id);
    }
    return false;
  }
  
  public function widgetdeleted($idwidget) {
    $deleted = false;
    foreach ($this->items as &$viewitem) {
      unset($sidebar);
      foreach ($viewitem['sidebars'] as &$sidebar) {
        for ($i = count($sidebar) - 1; $i >= 0; $i--) {
          if ($idwidget == $sidebar[$i]['id']) {
            array_delete($sidebar, $i);
            $deleted = true;
          }
        }
      }
    }
    if ($deleted) $this->save();
  }
  
}//class


class tevents_itemplate extends tevents {
  
  protected function create() {
    parent::create();
    $this->data['idview'] = 1;
  }
  
public function gethead() {}
public function getkeywords() {}
public function getdescription() {}
  
  public function getidview() {
    return $this->data['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->idview) {
      $this->data['idview'] = $id;
      $this->save();
    }
  }
  
  public function getview() {
    return tview::getview($this);
  }
  
}//class

class titems_itemplate extends titems {
  
  protected function create() {
    parent::create();
    $this->data['idview'] = 1;
    $this->data['keywords'] = '';
    $this->data['description'] = '';
    $this->data['head'] = '';
  }
  
  public function gethead() {
    return $this->data['head'];
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
    if ($id != $this->data['idview']) {
      $this->data['idview'] = $id;
      $this->save();
    }
  }
  
  public function getview() {
    return tview::getview($this);
  }
  
}//class