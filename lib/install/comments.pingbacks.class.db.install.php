<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tpingbacksInstall($self) {
  $manager = tdbmanager ::i();
  $dir = dirname(__file__) . '/sql/';
  $manager->CreateTable($self->table, file_get_contents($dir .'comments.pingbacks.sql'));
  
  $posts = tposts::i();
  $posts->deleted = $self->postdeleted;
}

function tpingbacksUninstall($self) {
  tposts::unsub($self);
}