<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdownloaditem extends tpost {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getchildtable() {
    return 'downloaditems';
  }
  
  public static function selectitems(array $items) {
    return self::select_child_items('tickets', $items);
  }
  
  protected function create() {
    parent::create();
    $this->data['childdata'] = &$this->childdata;
    $this->childdata = array(
    'id' => 0,
    'type' => 'theme',
    'downloads' => 0,
    'downloadurl'  => '',
    'authorurl'  => '',
    'authorname' => '',
    'version'=> '1.00',
    'votes' => 0,
    'poll' => 0
    );
  }
  
  protected function getauthorname() {
    return $this->childdata['authorname'];
  }
  
  public function getparenttag() {
    return $this->type == 'theme' ? litepublisher::$options->downloaditem_themetag : litepublisher::$options->downloaditem_plugintag;
  }
  
  public function settagnames($names) {
    $names = trim($names);
    if ($names == '') {
      $this->tags = array();
      return;
    }
    $parent = $this->getparenttag();
    $tags = ttags::i();
    $items = array();
    $list = explode(',', trim($names));
    foreach ($list as $title) {
      $title = tcontentfilter::escape($title);
      if ($title == '') continue;
      $items[] = $tags->add($parent, $title);
    }
    
    $this->tags=  $items;
  }
  
  public function get_excerpt() {
    return $this->getdownloadcontent() . $this->data['excerpt'];
  }
  
  protected function getcontentpage($page) {
    $result = $this->theme->templates['custom']['siteform'];
    $result .= $this->getdownloadcontent();
    if ($this->poll > 0) {
      $polls = tpolls::i();
      $result .= $polls->gethtml($this->poll, true);
    }
    
    $result .= parent::getcontentpage($page);
    return $result;
  }
  
  public function getdownloadcontent() {
    ttheme::$vars['lang'] = tlocal::i('downloaditem');
    ttheme::$vars['post'] = $this;
    $theme = $this->theme;
    return $theme->parse($theme->templates['custom']['downloaditem']);
  }
  
  public function getdownloadcount() {
    return sprintf(tlocal::get('downloaditem', 'downloaded'), $this->downloads);
  }
  
  public function closepoll() {
    $polls = tpolls::i();
    $polls->db->setvalue($this->poll, 'status', 'closed');
  }
  
}//class