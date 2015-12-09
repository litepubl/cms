<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
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
//possible values: default, lite, card
'postanounce' => 'default',
'invertorder' => false,
'perpage' => 0,

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
