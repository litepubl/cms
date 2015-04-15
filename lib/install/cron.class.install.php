<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcronInstall($self) {
  $manager = tdbmanager ::i();
  $manager->CreateTable('cron', file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'cron.sql'));
  
  litepublisher::$urlmap->add('/croncron.htm', get_class($self), null, 'get');
  
  $self->password =  md5uniq();
  $self->addnightly('tdboptimizer', 'optimize', null);
  $self->save();
}

function tcronUninstall($self) {
  turlmap::unsub($self);
}