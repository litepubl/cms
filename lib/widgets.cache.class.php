<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class twidgetscache extends titems {
  private $modified;
  
  public static function i($id = null) {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->modified = false;
  }
  
  public function getbasename() {
    $theme = ttheme::i();
    return 'widgetscache.' . $theme->name;
  }
  
  public function load() {
    if ($s = litepublisher::$urlmap->cache->get($this->getbasename() .'.php')) {
      return $this->loadfromstring($s);
    }
    return false;
  }
  
  public function savemodified() {
    if ($this->modified) {
      litepublisher::$urlmap->cache->set($this->getbasename(), $this->savetostring());
    }
    $this->modified = false;
  }
  
  public function save() {
    if (!$this->modified) {
      litepublisher::$urlmap->onclose = array($this, 'savemodified');
      $this->modified = true;
    }
  }
  
  public function getcontent($id, $sidebar, $onlybody = true) {
    if (isset($this->items[$id][$sidebar])) return $this->items[$id][$sidebar];
    return $this->setcontent($id, $sidebar, $onlybody);
  }
  
  public function setcontent($id, $sidebar, $onlybody = true) {
    $widget = twidgets::i()->getwidget($id);
    
    if ($onlybody) {
      $result = $widget->getcontent($id, $sidebar);
    } else {
      $result = $widget->getwidget($id, $sidebar);
    }
    
    $this->items[$id][$sidebar] = $result;
    $this->save();
    return $result;
  }
  
  public function expired($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
    }
  }
  
  public function onclearcache() {
    $this->items = array();
    $this->modified = false;
  }
  
}//class