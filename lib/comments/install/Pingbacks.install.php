<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\comments;
use litepubl\post\Posts;

function PingbacksInstall($self) {
    $manager = $self->bb->man;
    $manager->CreateTable($self->table, file_get_contents(__DIR__ . '/sql/pingbacks.sql'));

    $posts = Posts::i();
    $posts->deleted = $self->postdeleted;
}

function PingbacksUninstall($self) {
    Posts::unsub($self);
}