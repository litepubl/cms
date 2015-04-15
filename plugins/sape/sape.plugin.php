<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsapeplugin extends twidget {
  public $sape;
  public $counts;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.sape';
    $this->cache = 'nocache';
    $this->adminclass = 'tadminsapeplugin';
    $this->data['user'] = '';
    $this->data['count'] = 2;
    $this->data['force'] = false;
    $this->addmap('counts', array());
  }
  
  public function getdeftitle() {
    return tlocal::get('default', 'links');
  }
  
  private function createsape() {
    if (!defined('_SAPE_USER')){
      define('_SAPE_USER', $this->user);
      litepublisher::$classes->include_file(litepublisher::$paths->plugins . 'sape' . DIRECTORY_SEPARATOR . 'sape.php');
      $o['charset'] = 'UTF-8';
      $o['multi_site'] = true;
      if ($this->force) $o['force_show_code'] = $this->force;
      $this->sape = new SAPE_client($o);
    }
  }
  
  public function getwidget($id, $sidebar) {
    if ($this->user == '') return '';
    if (litepublisher::$urlmap->is404 || litepublisher::$urlmap->adminpanel) return '';
    return parent::getwidget($id, $sidebar);
  }
  
  public function getcontent($id, $sidebar) {
    $links = $this->getlinks();
    if (empty($links)) return '';
    return sprintf('<ul><li>%s</li></ul>', $links);
  }
  
  public function getcont() {
    return $this->getcontent(0, 0);
  }
  public function getlinks() {
    if ($this->user == '') return '';
    if (litepublisher::$urlmap->is404 || litepublisher::$urlmap->adminpanel) return '';
    if (!isset($this->sape)) $this->createsape();
    return $this->sape->return_links($this->counts[$id]);
  }
  
  public function setcount($id ,$count) {
    $this->counts[$id] = $count;
    $widgets = twidgets::i();
    foreach ($this->counts as $id => $count) {
      if (!isset($widgets->items[$id])) unset($this->counts[$id]);
    }
    $this->save();
  }
  
  public function add() {
    $id = $this->addtosidebar(0);
    $this->counts[$id] = 10;
    $this->save();
    return $id;
  }
  
}//class
?>