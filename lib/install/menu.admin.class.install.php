<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminmenusInstall($self) {
  if ('tadminmenus' != get_class($self)) return;
  $self->lock();
  $self->heads = '<link type="text/css" href="$site.files$template.cssmerger_admin" rel="stylesheet" />
  <script type="text/javascript" src="$site.files$template.jsmerger_admin"></script>';
  
  //posts
  $posts = $self->createitem(0, 'posts', 'author', 'tadminposts');
  {
    $id = $self->createitem($posts, 'editor', 'author', 'tposteditor');
    $self->items[$id]['title'] = tlocal::i()->newpost;
    $self->createitem($posts, 'addcat', 'editor', 'tadmintags');
    $self->createitem($posts, 'categories', 'editor', 'tadmintags');
    $self->createitem($posts, 'addtag', 'editor', 'tadmintags');
    $self->createitem($posts, 'tags', 'editor', 'tadmintags');
    $self->createitem($posts, 'staticpages', 'editor', 'tadminstaticpages');
  }
  
  $moder = $self->createitem(0, 'comments', 'commentator', 'tadminmoderator');
  {
    $self->createitem($moder, 'hold', 'commentator', 'tadminmoderator');
    $self->createitem($moder, 'authors', 'moderator', 'tadmincomusers');
    $self->createitem($moder, 'pingback', 'moderator', 'tadminpingbacks');
  }
  
  $plugins = $self->createitem(0, 'plugins', 'admin', 'tadminplugins');
  $files = $self->createitem(0, 'files', 'author', 'tadminfiles');
  {
    $self->createitem($files, 'thumbnail', 'editor', 'tadminfilethumbnails');
    $self->createitem($files, 'image', 'editor', 'tadminfiles');
    $self->createitem($files, 'video', 'editor', 'tadminfiles');
    $self->createitem($files, 'audio', 'editor', 'tadminfiles');
    $self->createitem($files, 'icon', 'editor', 'tadminfiles');
    $self->createitem($files, 'deficons', 'editor', 'tadminicons');
    $self->createitem($files, 'bin', 'editor', 'tadminfiles');
  }
  
  $views = $self->createitem(0, 'views', 'admin', 'tadminviews');
  {
    $self->createitem($views, 'addview', 'admin', 'tadminviews');
    $self->createitem($views, 'themes', 'admin', 'tadminthemes');
    $self->createitem($views, 'themefiles', 'admin', 'tadminthemefiles');
    $self->createitem($views, 'widgets', 'admin', 'tadminwidgets');
    $self->createitem($views, 'addcustom', 'admin', 'tadminwidgets');
    $self->createitem($views, 'group', 'admin', 'tadminviews');
    $self->createitem($views, 'defaults', 'admin', 'tadminviews');
    $self->createitem($views, 'spec', 'admin', 'tadminviews');
    $self->createitem($views, 'headers', 'admin', 'tadminviews');
    $self->createitem($views, 'jsmerger', 'admin', 'tadminjsmerger');
    $self->createitem($views, 'cssmerger', 'admin', 'tadmincssmerger');
  }
  
  $menu = $self->createitem(0, 'menu', 'editor', 'tadminmenumanager');
  {
    $id = $self->createitem($menu, 'edit', 'editor', 'tadminmenumanager');
    $self->items[$id]['title'] = tlocal::get('menu', 'addmenu');
    $id = $self->createitem($menu, 'editfake', 'editor', 'tadminmenumanager');
    $self->items[$id]['title'] = tlocal::get('menu', 'addfake');
  }
  
  $opt = $self->createitem(0, 'options', 'admin', 'tadminoptions');
  {
    $self->createitem($opt, 'home', 'admin', 'tadminoptions');
    $self->createitem($opt, 'mail', 'admin', 'tadminoptions');
    $self->createitem($opt, 'rss', 'admin', 'tadminoptions');
    $self->createitem($opt, 'view', 'admin', 'tadminoptions');
    $self->createitem($opt, 'files', 'admin', 'tadminoptions');
    $self->createitem($opt, 'comments', 'admin', 'tadmincommentmanager');
    $self->createitem($opt, 'ping', 'admin', 'tadminoptions');
    $self->createitem($opt, 'links', 'admin', 'tadminoptions');
    $self->createitem($opt, 'cache', 'admin', 'tadminoptions');
    $self->createitem($opt, 'catstags', 'admin', 'tadminoptions');
    $self->createitem($opt, 'secure', 'admin', 'tadminoptions');
    $self->createitem($opt, 'robots', 'admin', 'tadminoptions');
    $self->createitem($opt, 'local', 'admin', 'tadminlocalmerger');
    $self->createitem($opt, 'notfound404', 'admin', 'tadminoptions');
    $self->createitem($opt, 'redir', 'admin', 'tadminredirector');
  }
  
  $service = $self->createitem(0, 'service', 'admin', 'tadminservice');
  {
    $self->createitem($service, 'backup', 'admin', 'tadminservice');
    $self->createitem($service, 'upload', 'admin', 'tadminservice');
    $self->createitem($service, 'run', 'admin', 'tadminservice');
  }
  
  $id = $self->addfake('/admin/logout/', tlocal::i()->logout);
  $self->items[$id]['order'] = 9999999;
  
  /*
  $board = $self->additem(array(
  'parent' => 0,
  'url' => '/admin/',
  'title' => tlocal::get('adminmenus', 'board'),
  'name' => 'board',
  'class' => 'tadminboard',
  'group' => 'author'
  ));
  */
  $self->unlock();
  
  $redir = tredirector::i();
  $redir->add('/admin/', '/admin/posts/editor/');
}

function  tadminmenusUninstall($self) {
  //rmdir(. 'menus');
}

?>