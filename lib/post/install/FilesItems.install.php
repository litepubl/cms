<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\post;
use litepubl\core\litepubl;

function FilesItemsInstall($self) {
    $manager = $self->db->man;
    $manager->createtable($self->table, file_get_contents(litepubl::$paths->lib . 'core/install/sql/ItemsPosts.sql'));

    $posts = Posts::i();
    $posts->deleted = $self->deletepost;
}

function FilesitemsUninstall($self) {
   Posts::unsub($self);
}