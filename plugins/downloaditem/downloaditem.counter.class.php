<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdownloaditemcounter extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'downloaditems';
  }
  
  public function updatestat() {
    $filename = litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR . 'downloaditemscount.txt';
    if (@file_exists($filename) && ($s = @file_get_contents($filename))) {
      @unlink($filename);
      $stat = array();
      $a = explode("\n", $s);
      foreach ($a as $id) {
        $id = (int) $id;
        if ($id == 0) continue;
        if (isset($stat[$id])) {
          $stat[$id]++;
        } else {
          $stat[$id] = 1;
        }
      }
      if (count($stat) == 0) return;
      $this->loaditems(array_keys($stat));
      $db = $this->db;
      foreach ($stat as $id => $downloads) {
        $db->setvalue($id, 'downloads', $downloads + $this->items[$id]['downloads']);
      }
    }
  }
  
  public function request($arg) {
    //$this->cache = false;
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if (!$this->itemexists($id)) return 404;
    $item = $this->getitem($id);
    $url = $item['downloadurl'];
    $filename = litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR . 'downloaditemscount.txt';
    return "<?php tfiler::append('$id\n', '$filename');
    return litepublisher::\$urlmap->redir('$url');";
  }
  
}//class