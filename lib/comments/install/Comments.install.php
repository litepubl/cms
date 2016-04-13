<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\comments;
use litepubl\post\Posts;

function CommentsInstall($self) {
    $manager = $self->db->man;
    $dir = dirname(__file__) . '/sql/';
    $manager->CreateTable($self->table, file_get_contents($dir . 'comments.sql'));
    $manager->CreateTable($self->rawtable, file_get_contents($dir . 'raw.sql'));

    Posts::i()->deleted = $self->postdeleted;
}

function CommentsUninstall($self) {
    Posts::unsub($self);
}