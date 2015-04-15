<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tuserpagesInstall($self) {
  if ($self->dbversion) {
    $manager = tdbmanager::i();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'userpage.sql'));
  }
  
  $v = $self->createpage;
  $self->lock();
  $self->createpage = false;
  $self->add(1, 'Admin', litepublisher::$options->email, litepublisher::$site->url . '/');
  $itemurl = litepublisher::$urlmap->findurl('/');
  $self->setvalue(1, 'idurl', $itemurl['id']);
  $self->createpage = $v;
  $self->unlock();
  
  $linkgen = tlinkgenerator::i();
  $linkgen->data['user'] = '/user/[name].htm';
  $linkgen->save();
  
  litepublisher::$urlmap->add('/users.htm', get_class($self), 'url', 'get');
  
  $robots = trobotstxt ::i();
  $robots->AddDisallow('/users.htm');
}

function tuserpagesUninstall($self) {  turlmap::unsub($self);
  turlmap::unsub($self);
}