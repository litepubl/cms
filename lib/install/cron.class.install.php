<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tcronInstall($self) {
  $manager = tdbmanager::i();
  $manager->CreateTable('cron', file_get_contents(dirname(__file__) . '/sql/cron.sql'));

  litepubl::$urlmap->add('/croncron.htm', get_class($self) , null, 'get');

  $self->password = md5uniq();
  $self->addnightly('litepubl\turlmap', 'updatefilter', null);
  $self->addnightly('litepubl\tdboptimizer', 'optimize', null);
  $self->save();
}

function tcronUninstall($self) {
  turlmap::unsub($self);
}