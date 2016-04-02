<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tcommentsInstall($self) {
  $manager = tdbmanager::i();
  $dir = dirname(__file__) . '/sql/';
  $manager->CreateTable($self->table, file_get_contents($dir . 'comments.sql'));
  $manager->CreateTable($self->rawtable, file_get_contents($dir . 'comments.raw.sql'));

  tposts::i()->deleted = $self->postdeleted;
}

function tcommentsUninstall($self) {
  tposts::unsub($self);

}