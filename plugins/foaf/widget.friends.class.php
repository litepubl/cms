<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfriendswidget extends twidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.friends';
    $this->template = 'friends';
    $this->adminclass = 'tadminfriendswidget';
    $this->data['maxcount'] =0;
    $this->data['redir'] = true;
    $this->data['redirlink'] = '/foaflink.htm';
  }
  
  public function getdeftitle() {
    $about = tplugins::getabout(tplugins::getname(__file__));
    return $about['name'];
  }
  
  public function getcontent($id, $sidebar) {
    $foaf = tfoaf::i();
    $items = $foaf->getapproved($this->maxcount);
    if (count($items) == 0) return '';
    $result = '';
    $url = litepublisher::$site->url;
    $redirlink  = litepublisher::$site->url . $this->redirlink . litepublisher::$site->q . 'id=';
    $theme = ttheme::i();
    $tml = $theme->getwidgetitem('friends', $sidebar);
    $args = targs::i();
    $args->subcount = '';
    $args->subitems = '';
    $args->$icon = '';
    $args->rel = 'friend';
    foreach ($items as $id) {
      $item = $foaf->getitem($id);
      $args->add($item);
      $args->anchor = $item['title'];
      if ($this->redir && !strbegin($item['url'], $url)) {
        $args->url = $redirlink . $id;
      }
      $result .=   $theme->parsearg($tml, $args);
    }
    
    return $theme->getwidgetcontent($result, 'friends', $sidebar);
  }
  
  public function request($arg) {
    $id = empty($_GET['id']) ? 1 : (int) $_GET['id'];
    $foaf = tfoaf::i();
    if (!$foaf->itemexists($id)) return 404;
    $item = $foaf->getitem($id);
    $this->cache = false;
    return sprintf('<?php litepublisher::$urlmap->redir(\'%s\'); ?>', $item['url']);
  }
  
}//class