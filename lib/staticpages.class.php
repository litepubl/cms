<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tstaticpages extends titems implements itemplate {
  private $id;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'staticpages';
  }
  
  public function request($arg) {
    $this->id = (int)$arg;
  }
  
  public function getval($name) {
    return $this->items[$this->id][$name];
  }
  
  public function gettitle() {
    return $this->getval('title');
  }
  
public function gethead() { }
  public function getkeywords() {
    return $this->getval('keywords');
  }
  
  public function getdescription() {
    return $this->getval('description');
  }
  
  public function getidview() {
    return $this->getval('idview');
  }
  
  public function setidview($id) {
    if ($id != $this->idview) {
      $this->items[$this->id]['idview'] = $id;
      $this->save();
    }
  }
  
  public function getcont() {
    $theme = tview::getview($this)->theme;
    return $theme->simple($this->getval('filtered'));
  }
  
  public function add($title, $description, $keywords, $content) {
    $filter = tcontentfilter::i();
    $title = tcontentfilter::escape($title);
    $linkgen = tlinkgenerator::i();
    $url = $linkgen->createurl($title, 'menu', true);
    $urlmap = turlmap::i();
    $this->items[++$this->autoid] = array(
    'idurl' => $urlmap->add($url, get_class($this),  $this->autoid),
    'url' => $url,
    'title' => $title,
    'filtered' => $filter->filter($content),
    'rawcontent' => $content,
    'description' => tcontentfilter::escape($description),
    'keywords' => tcontentfilter::escape($keywords),
    'idview' => 1
    );
    $this->save();
    return $this->autoid;
  }
  
  public function edit($id, $title, $description, $keywords, $content) {
    if (!$this->itemexists($id)) return false;
    $filter = tcontentfilter::i();
    $item = $this->items[$id];
    $this->items[$id] = array(
    'idurl' => $item['idurl'],
    'url' => $item['url'],
    'title' => $title,
    'filtered' => $filter->filter($content),
    'rawcontent' => $content,
    'description' => tcontentfilter::escape($description),
    'keywords' => tcontentfilter::escape($keywords),
    'idview' => $item['idview']
    );
    $this->save();
    litepublisher::$urlmap->clearcache();
  }
  
  public function delete($id) {
    $urlmap = turlmap::i();
    $urlmap->deleteitem($this->items[$id]['idurl']);
    parent::delete($id);
  }
  
}//class