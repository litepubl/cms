<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function tdownloadcounterInstall($self) {
  if (dbversion) {
    $manager = TDBManager::i();
    $dir = dirname(__file__) . '/sql/';
    $manager->CreateTable($self->table, file_get_contents($dir . 'downloadcounter.sql'));
  }

  $files = tfiles::i();
  $files->deleted = $self->delete;

  $urlmap = turlmap::i();
  $urlmap->add('/downloadcounter/', get_class($self) , null, 'get');
}

function tdownloadcounterUninstall($self) {
  turlmap::unsub($self);
  $files = tfiles::i();
  $files->unbind($self);
}