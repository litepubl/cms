<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl\plugins;
use litepubl;

function twikiwordsInstall($self) {
  if ($self->dbversion) {
    $manager = tdbmanager::i();
    $manager->createtable($self->table, "  `id` int(10) unsigned NOT NULL auto_increment,
    `word` text NOT NULL,
    PRIMARY KEY  (`id`)");

    $manager->createtable($self->itemsposts->table, file_get_contents(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'items.posts.sql'));
  }

  $filter = tcontentfilter::i();
  $filter->beforecontent = $self->beforefilter;

  $posts = tposts::i();
  $posts->deleted = $self->postdeleted;

  litepublisher::$classes->classes['wikiwords'] = get_class($self);
  litepublisher::$classes->save();
}

function twikiwordsUninstall($self) {
  unset(litepublisher::$classes->classes['wikiword']);
  litepublisher::$classes->save();

  $filter = tcontentfilter::i();
  $filter->unbind($self);

  tposts::unsub($self);
  if ($self->dbversion) {
    $manager = tdbmanager::i();
    $manager->deletetable($self->table);
    $manager->deletetable($self->itemsposts->table);
  }
}