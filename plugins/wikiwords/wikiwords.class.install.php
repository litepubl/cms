<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function twikiwordsInstall($self) {
    if ($self->dbversion) {
        $manager = tdbmanager::i();
        $manager->createtable($self->table, "  `id` int(10) unsigned NOT NULL auto_increment,
    `word` text NOT NULL,
    PRIMARY KEY  (`id`)");

        $manager->createtable($self->itemsposts->table, file_get_contents(litepubl::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'items.posts.sql'));
    }

    $filter = tcontentfilter::i();
    $filter->beforecontent = $self->beforefilter;

    $posts = tposts::i();
    $posts->deleted = $self->postdeleted;

    litepubl::$classes->classes['wikiwords'] = get_class($self);
    litepubl::$classes->save();
}

function twikiwordsUninstall($self) {
    unset(litepubl::$classes->classes['wikiword']);
    litepubl::$classes->save();

    $filter = tcontentfilter::i();
    $filter->unbind($self);

    tposts::unsub($self);
    if ($self->dbversion) {
        $manager = tdbmanager::i();
        $manager->deletetable($self->table);
        $manager->deletetable($self->itemsposts->table);
    }
}