<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tforumInstall($self) {
  litepublisher::$options->reguser = true;
  tadminoptions::i()->usersenabled = true;
  
  $name = basename(dirname(__file__));
  tlocalmerger::i()->addplugin($name);
  
  $lang = tlocal::admin('forum');
  
  //prevent double create view
  $idview = 0;
  $views = tviews::i();
  foreach ($views->items as $id => $item) {
    if ('forum' == $item['themename']) {
      $idview = $id;
      break;
    }
  }
  
  if (!$idview) {
    $view = new tview();
    $view->name = $lang->forum;
    $view->themename = 'forum';
    $idview = $views->addview($view);
  }
  
  $lang->section = 'forum';
  $cats = tcategories::i();
  $idcat = $cats->add(0, $lang->forum);
  $cats->setvalue($idcat, 'includechilds', 1);
  $cats->setvalue($idcat, 'idview', $idview);
  $cats->contents->setcontent($idcat, $lang->intro .
  sprintf(' <a href="%s/admin/plugins/forum/">%s</a>', litepublisher::$site->url, tlocal::get('names', 'adminpanel')));
  
  $self->rootcat = $idcat;
  $self->idview = $idview;
  $self->categories_changed();
  $self->save();
  
  $cat = $cats->getitem($idcat);
  
  tmenus::i()->addfake($cat['url'], $cat['title']);
  tjsmerger::i()->add('default', '/plugins/forum/forum.min.js');
  
  $linkgen = tlinkgenerator::i();
  $linkgen->data['forum'] = '/forum/[title].htm';
  $linkgen->save();
  
  $cats = tcategories::i();
  $cats->lock();
  $cats->changed = $self->categories_changed;
  $cats->added = $self->catadded;
  $cats->unlock();
  tthemeparser::i()->parsed = $self->themeparsed;
  ttheme::clearcache();
  
  litepublisher::$classes->add('tforumeditor', 'admin.forumeditor.class.php', $name);
  $adminmenus = tadminmenus::i();
  $adminmenus->createitem($adminmenus->url2id('/admin/plugins/'),
  'forum', 'author', 'tforumeditor');
}

function tforumUninstall($self) {
  tcategories::i()->unbind($self);
  tthemeparser::i()->unbind($self);
  ttheme::clearcache();
  
  tlocalmerger::i()->deleteplugin(basename(dirname(__file__)));
  tjsmerger::i()->deletefile('default', '/plugins/forum/forum.min.js');
  
  $item = tcategories::i()->getitem($self->rootcat);
  $menus = tmenus::i();
  while ($menus->deleteurl($item['url']));
  
  $adminmenus = tadminmenus::i();
  $adminmenus->deletetree($adminmenus->url2id('/admin/plugins/forum/'));
  
  litepublisher::$classes->delete('tforumeditor');
  
  $linkgen = tlinkgenerator::i();
  unset($linkgen->data['forum']);
  $linkgen->save();
}