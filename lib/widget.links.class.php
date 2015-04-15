<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlinkswidget extends twidget {
  public $items;
  public $autoid;
  public $redirlink;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('added', 'deleted');
    $this->basename = 'widgets.links';
    $this->template = 'links';
    $this->adminclass = 'tadminlinkswidget';
    $this->addmap('items', array());
    $this->addmap('autoid', 0);
    $this->redirlink = '/linkswidget/';
    $this->data['redir'] = false;
  }
  
  public function getdeftitle() {
    return tlocal::get('default', 'links');
  }
  
  public function getcontent($id, $sidebar) {
    if (count($this->items) == 0) return '';
    $result = '';
    $theme = ttheme::i();
    $tml = $theme->getwidgetitem('links', $sidebar);
    $redirlink = litepublisher::$site->url . $this->redirlink . litepublisher::$site->q . 'id=';
    $url = litepublisher::$site->url;
    $args = targs::i();
    $args->subcount = '';
    $args->subitems = '';
    $args->icon = '';
    $args->rel = 'link';
    foreach ($this->items as $id => $item) {
      $args->add($item);
      $args->id = $id;
      if ($this->redir && !strbegin($item['url'], $url)) {
        $args->link = $redirlink . $id;
      } else {
        $args->link = $item['url'];
      }
      $result .=   $theme->parsearg($tml, $args);
    }
    
    return $theme->getwidgetcontent($result, 'links', $sidebar);
  }
  
  public function add($url, $title, $text) {
    $this->items[++$this->autoid] = array(
    'url' => $url,
    'title' => $title,
    'text' => $text
    );
    
    $this->save();
    $this->added($this->autoid);
    return $this->autoid;
  }
  
  public function edit($id, $url, $title, $text) {
    $id = (int) $id;
    if (!isset($this->items[$id])) return false;
    $this->items[$id] = array(
    'url' => $url,
    'title' => $title,
    'text' => $text
    );
    $this->save();
  }
  
  public function delete($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
      litepublisher::$urlmap->clearcache();
    }
  }
  
  public function request($arg) {
    $this->cache = false;
    $id = empty($_GET['id']) ? 1 : (int) $_GET['id'];
    if (!isset($this->items[$id])) return 404;
    return '<?php litepublisher::$urlmap->redir(\'' . $this->items[$id]['url'] . '\'); ?>';
  }
  
}//class