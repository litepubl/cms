<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function twidgetUninstall($self) {
  twidgets::i()->deleteclass(get_class($self));
}

function twidgetsInstall($self) {
  litepublisher::$urlmap->addget('/getwidget.htm', get_class($self));
  $robot = trobotstxt::i();
  $robot->AddDisallow('/getwidget.htm');
  
  $xmlrpc = TXMLRPC::i();
  $xmlrpc->add('litepublisher.getwidget', 'xmlrpcgetwidget', get_class($self));
  install_std_widgets($self);
}

function twidgetsUninstall($self) {
  turlmap::unsub($self);
  $xmlrpc = TXMLRPC::i();
  $xmlrpc->deleteclass(get_class($self));
}

function twidgetscacheInstall($self) {
  litepublisher::$urlmap->onclearcache = $self->onclearcache;
}

function twidgetscacheUninstall($self) {
  turlmap::unsub($self);
}

function install_std_widgets($widgets) {
  $widgets->lock();
  $sidebars = tsidebars::i();
  
  $id = $widgets->add(tcategorieswidget::i());
  $sidebars->insert($id, 'inline', 0, -1);
  
  $id = $widgets->add(ttagswidget::i());
  
  $id = $widgets->add(tarchiveswidget::i());
  $sidebars->insert($id, 'inline', 0, -1);
  
  $id = $widgets->add(tlinkswidget::i());
  $sidebars->insert($id, 'inline', 0, -1);
  
  $id = $widgets->add(tpostswidget::i());
  $sidebars->insert($id, 'inline', 1, -1);
  
  $id = $widgets->add(tcommentswidget::i());
  $sidebars->insert($id, true, 1, -1);
  
  $id = $widgets->add(tmetawidget::i());
  $sidebars->insert($id, 'inline', 1, -1);
  
  $widgets->unlock();
}
?>