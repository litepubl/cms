<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function tsamepostsInstall($self) {
    if (dbversion) {
        $manager = tdbmanager::i();
        $manager->createtable($self->table, 'id int UNSIGNED NOT NULL default 0,
    items text NOT NULL,
    PRIMARY KEY(id) ');
    }

    $widgets = twidgets::i();
    $widgets->addclass($self, 'tpost');

    $posts = tposts::i();
    $posts->changed = $self->postschanged;
}

function tsamepostsUninstall($self) {
    tposts::unsub($self);
    twidgets::i()->deleteclass(get_class($self));

    if (dbversion) {
        $manager = tdbmanager::i();
        $manager->deletetable($self->table);
    } else {
        $posts = tposts::i();
        $dir = litepubl::$paths->data . 'posts' . DIRECTORY_SEPARATOR;
        foreach ($posts->items as $id => $item) {
            @unlink($dir . $id . DIRECTORY_SEPARATOR . 'same.php');
        }
    }
}