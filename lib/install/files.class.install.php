<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tfilesInstall($self) {
  $manager = tdbmanager ::i();
  $dir = dirname(__file__) . '/sql/';
  $manager->createtable($self->table, file_get_contents($dir .'files.sql'));
  $manager->createtable($self->itemsposts->table, file_get_contents($dir .'items.posts.sql'));
  $manager->createtable('imghashes', file_get_contents($dir .'imghashes.sql'));
  
  $posts= tposts::i();
  $posts->lock();
  $posts->added = $self->postedited;
  $posts->edited = $self->postedited;
  $posts->deleted = $self->itemsposts->deletepost;
  $posts->unlock();
}

function tfilesUninstall($self) {
  tposts::unsub($self);
  tposts::unsub($self->itemsposts);
}