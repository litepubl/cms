<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tview extends titem_storage {
  public $sidebars;
  protected $_theme;
  protected $_admintheme;

  public static function i($id = 1) {
    if ($id == 1) {
      $class = get_called_class();
    } else {
      $views = tviews::i();
      $class = $views->itemexists($id) ? $views->items[$id]['class'] : get_called_class();
    }

    return parent::iteminstance($class, $id);
  }

public function newitem($id) {
return litepubl::$classes->newitem(static::getinstancename() , get_called_class(), $id);
}

  public static function getinstancename() {
    return 'view';
  }

  public static function getview($instance) {
    $id = $instance->getidview();
    if (isset(static::$instances['view'][$id])) return static::$instances['view'][$id];
    $views = tviews::i();
    if (!$views->itemexists($id)) {
      $id = 1; //default, wich always exists
      $instance->setidview($id);
    }
    return static::i($id);
  }

  protected function create() {
    parent::create();
    $this->data = array(
      'id' => 0,
      'class' => get_class($this) ,
      'name' => 'default',
      'themename' => 'default',
      'adminname' => 'admin',
      'menuclass' => 'tmenus',
      'hovermenu' => true,
      'customsidebar' => false,
      'disableajax' => false,
      //possible values: default, lite, card
      'postanounce' => 'excerpt',
      'invertorder' => false,
      'perpage' => 0,

      'custom' => array() ,
      'sidebars' => array()
    );

    $this->sidebars = & $this->data['sidebars'];
    $this->_theme = null;
    $this->_admintheme = null;
  }

  public function __destruct() {
    $this->_theme = null;
    $this->_admintheme = null;
    parent::__destruct();
  }

  public function getowner() {
    return tviews::i();
  }

  public function load() {
    if (parent::load()) {
      $this->sidebars = & $this->data['sidebars'];
      return true;
    }
    return false;
  }

  protected function get_theme($name) {
    return ttheme::getinstance($name);
  }

  protected function get_admintheme($name) {
    return admintheme::getinstance($name);
  }

  public function setthemename($name) {
    if ($name == $this->themename) return false;
    if (strbegin($name, 'admin')) $this->error('The theme name cant begin with admin keyword');
    if (!basetheme::exists($name)) return $this->error(sprintf('Theme %s not exists', $name));

    $this->data['themename'] = $name;
    $this->_theme = $this->get_theme($name);
    $this->data['custom'] = $this->_theme->templates['custom'];
    $this->save();

    static::getowner()->themechanged($this);
  }

  public function setadminname($name) {
    if ($name != $this->adminname) {
      if (!strbegin($name, 'admin')) $this->error('Admin theme name dont start with admin keyword');
      if (!admintheme::exists($name)) return $this->error(sprintf('Admin theme %s not exists', $name));
      $this->data['adminname'] = $name;
      $this->_admintheme = $this->get_admintheme($name);
      $this->save();
    }
  }

  public function gettheme() {
    if ($this->_theme) {
      return $this->_theme;
    }

    if (ttheme::exists($this->themename)) {
      $this->_theme = $this->get_theme($this->themename);

      $viewcustom = & $this->data['custom'];
      $themecustom = & $this->_theme->templates['custom'];

      //aray_equal
      if ((count($viewcustom) == count($themecustom)) && !count(array_diff(array_keys($viewcustom) , array_keys($themecustom)))) {
        $this->_theme->templates['custom'] = $viewcustom;
      } else {
        $this->data['custom'] = $themecustom;
        $this->save();
      }
    } else {
      $this->setthemename('default');
    }
    return $this->_theme;
  }

  public function getadmintheme() {
    if ($this->_admintheme) {
      return $this->_admintheme;
    }

    if (!admintheme::exists($this->adminname)) {
      $this->setadminname('admin');
    }

    return $this->_admintheme = $this->get_admintheme($this->adminname);
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

} //class