<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class toldestposts extends tclasswidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.oldestposts';
    $this->template = 'posts';
    $this->adminclass = 'tadminoldestposts';
    $this->cache = 'nocache';
    $this->data['maxcount'] = 10;
  }
  
  public function getdeftitle() {
    return tlocal::get('default', 'prev');
  }
  
  public function getcontent($id, $sidebar) {
    $post = $this->getcontext('tpost');
    $posts = tposts::i();
    if (dbversion) {
      $items = $posts->finditems("status = 'published' and posted < '$post->sqldate' ",' order by posted desc limit '. $this->maxcount);
    } else {
      $arch = array_keys($posts->archives);
      $i = array_search($post->id, $arch);
      if (!is_int($i)) return '';
      $items = array_slice($arch, $i + 1, $this->maxcount);
    }
    
    if (count($items) == 0) return '';
    
    $theme = ttheme::i();
    return $theme->getpostswidgetcontent($items, $sidebar, '');
  }
  
}//class