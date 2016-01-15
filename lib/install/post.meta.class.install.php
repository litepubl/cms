<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function tmetapostInstall($self) {
  if (dbversion) {
    $dir = dirname(__file__) . '/sql/';
    $manager = tdbmanager::i();
    $manager->CreateTable($self->table, file_get_contents($dir . 'post.meta.sql'));
  }
}

function tmetapostUninstall($self) {
}