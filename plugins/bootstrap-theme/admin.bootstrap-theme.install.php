<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function admin_bootstrap_themeInstall($self) {
  $about =tplugins::getabout(tplugins::getname(__file__));
  
  $admin = tadminmenus::i();
  $admin->lock();
  $admin->additem(array(
  'parent' => $admin->url2id('/admin/views/'),
  'url' => '/admin/views/bootstraptheme/',
  'title' => $about['name'],
  'name' => 'bootstraptheme',
  'class' => get_class($self),
  'group' => 'admin'
  ));
  
  litepublisher::$classes->add('admin_bootstrap_header', 'admin.bootstrap-header.php', basename(dirname(__file__)));
  $admin->unlock();
}

function admin_bootstrap_themeUninstall($self) {
  $admin = tadminmenus::i();
  $admin->lock();
  $admin->deleteurl('/admin/views/bootstraptheme/');
  litepublisher::$classes->delete('admin_bootstrap_header');
  $admin->unlock();
}