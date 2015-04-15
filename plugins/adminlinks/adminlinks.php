<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincontextwidget extends torderwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.adminlinks';
    $this->cache = 'nocache';
    $this->adminclass = 'tadminorderwidget';
  }
  
  public function getdeftitle() {
    $about = tplugins::getabout(tplugins::getname(__file__));
    return $about['name'];
  }
  
  public function getwidget($id, $sidebar) {
    $links = '';
    $theme = ttheme::i();
    $tml = $theme->getwidgetitem('widget', $sidebar);
    tlocal::usefile('admin');
    
    if (litepublisher::$urlmap->context instanceof tpost) {
      $post = litepublisher::$urlmap->context;
      $lang = tlocal::i('posts');
      $title = $lang->adminpost;
      $action = tadminhtml::getadminlink('/admin/posts/', "id=$post->id&action");
      $links = $this->getitem($tml, tadminhtml::getadminlink('/admin/posts/editor/', 'id=' . $post->id), $lang->edit);
      $links .= $this->getitem($tml, "$action=delete", $lang->delete);
    } else {
      switch (get_class(litepublisher::$urlmap->context)) {
        case 'tcategories':
        case 'ttags':
        $tags = litepublisher::$urlmap->context;
        $name = $tags instanceof ttags ? 'tags' : 'categories';
        $adminurl = litepublisher::$site->url . "/admin/posts/$name/";
        $lang = tlocal::i('tags');
      $title = $lang->{$name};
        $links = $this->getitem($tml,$adminurl, $lang->add);
        $adminurl .= litepublisher::$site->q . "id=$tags->id";
        $links .= $this->getitem($tml,$adminurl, $lang->edit);
        $links .= $this->getitem($tml, "$adminurl&action=delete", $lang->delete);
        $links .= $this->getitem($tml, "$adminurl&full=1", $lang->fulledit);
        break;
        
        case 'thomepage':
        $lang = tlocal::i('options');
        $title = $lang->home;
        $links .= $this->getitem($tml, "/admin/options/home/", $lang->title);
        break;
      }
      
      if ((litepublisher::$urlmap->context instanceof tmenu) && !(litepublisher::$urlmap->context instanceof tadminmenu)) {
        $menu = litepublisher::$urlmap->context;
        $lang = tlocal::i('menu');
        $title = $lang->title;
        $adminurl = litepublisher::$site->url . "/admin/menu/edit/";
        $links .= $this->getitem($tml,$adminurl, $lang->addmenu);
        $links .= $this->getitem($tml, $adminurl . litepublisher::$site->q . "id=$menu->id", $lang->edit);
      }
    }
    
    if ($links == '') return '';
    $links .= $this->getitem($tml, '/admin/logout/', tlocal::get('login', 'logout'));
    $links = $theme->getwidgetcontent($links, 'widget', $sidebar);
    return $theme->getwidget($this->gettitle($id), $links, 'widget', $sidebar);
  }
  private function getitem($tml, $url, $title) {
    $args = targs::i();
    $args->subcount = '';
    $args->icon = '';
    $args->subitems = '';
    $args->rel = 'admin';
    if (strbegin($url, 'http://')) {
      $args->link = $url;
    } else {
      $args->url = $url;
    }
    $args->title = $title;
    $args->text = $title;
    $theme = ttheme::i();
    return $theme->parsearg($tml, $args);
  }
  
}//class