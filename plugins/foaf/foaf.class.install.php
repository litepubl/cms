<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tfoafInstall($self) {
  $merger = tlocalmerger::i();
  $merger->addplugin(tplugins::getname(__file__));
  
  $dir = dirname(__file__) .DIRECTORY_SEPARATOR  . 'resource' . DIRECTORY_SEPARATOR;
  $lang = tlocal::i('foaf');
  
  if ($self->dbversion) {
    $manager = tdbmanager ::i();
    $manager->createtable($self->table, file_get_contents($dir .'foaf.sql'));
  }
  
  $actions = TXMLRPCAction ::i();
  $actions->lock();
  $actions->add('invatefriend', get_class($self), 'Invate');
  $actions->add('rejectfriend', get_class($self), 'Reject');
  $actions->add('acceptfriend', get_class($self), 'Accept');
  $actions->unlock();
  
  $urlmap = litepublisher::$urlmap;
  $urlmap->lock();
  $urlmap->add('/foaf.xml', get_class($self), null);
  
  $name = tplugins::getname(__file__);
  $classes = litepublisher::$classes;
  $classes->lock();
  $classes->add('tadminfoaf', 'admin.foaf.class.php', $name);
  $classes->add('tfoafutil', 'foaf.util.class.php', $name);
  $classes->add('tprofile', 'profile.class.php', $name);
  $classes->add('tfriendswidget', 'widget.friends.class.php', $name);
  $classes->unlock();
  
  $admin = tadminmenus::i();
  $admin->lock();
  $id = $admin->createitem(0, 'foaf', 'admin', 'tadminfoaf');
  {
    $admin->createitem($id, 'profile', 'admin', 'tadminfoaf');
    $admin->createitem($id, 'profiletemplate', 'admin', 'tadminfoaf');
  }
  $admin->unlock();
  $urlmap->unlock();
  
  $template = ttemplate::i();
  $template->addtohead('	<link rel="meta" type="application/rdf+xml" title="FOAF" href="$site.url/foaf.xml" />');
  $about = tplugins::getabout($name);
  $meta = tmetawidget::i();
  $meta->lock();
  $meta->add('foaf', '/foaf.xml', $about['name']);
  $meta->add('profile', '/profile.htm', $lang->profile);
  $meta->unlock();
  ttheme::clearcache();
}

function tfoafUninstall($self) {
  $merger = tlocalmerger::i();
  $merger->deleteplugin(tplugins::getname(__file__));
  
  $actions = TXMLRPCAction ::i();
  $actions->deleteclass(get_class($self));
  
  $urlmap = litepublisher::$urlmap;
  $urlmap->lock();
  turlmap::unsub($self);
  
  $classes = litepublisher::$classes;
  $classes->lock();
  $classes->delete('tfoafutil');
  $classes->delete('tprofile');
  $classes->delete('tfriendswidget');
  $classes->delete('tadminfoaf');
  $classes->unlock();
  
  $admin = tadminmenus::i();
  $admin->lock();
  $admin->deleteurl('/admin/foaf/profiletemplate/');
  $admin->deleteurl('/admin/foaf/profile/');
  $admin->deleteurl('/admin/foaf/');
  $admin->unlock();
  
  $urlmap->unlock();
  
  if ($self->dbversion) {
    $manager = tdbmanager ::i();
    $manager->deletetable($self->table);
  }
  
  $template = ttemplate::i();
  $template->deletefromhead('	<link rel="meta" type="application/rdf+xml" title="FOAF" href="$site.url/foaf.xml" />');
  
  $meta = tmetawidget::i();
  $meta->lock();
  $meta->delete('foaf');
  $meta->delete('profile');
  $meta->unlock();
  
  ttheme::clearcache();
}