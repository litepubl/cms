<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tdownloadcounterInstall($self) {
  if (dbversion) {
    $manager = TDBManager ::i();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'downloadcounter.sql'));
  }
  
  $files = tfiles::i();
  $files->deleted = $self->delete;
  
  $urlmap = turlmap::i();
  $urlmap->add('/downloadcounter/', get_class($self), null, 'get');
}

function tdownloadcounterUninstall($self) {
  turlmap::unsub($self);
  $files = tfiles::i();
  $files->unbind($self);
}