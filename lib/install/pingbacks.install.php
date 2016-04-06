<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tpingbacksInstall($self) {
    $manager = tdbmanager::i();
    $dir = dirname(__file__) . '/sql/';
    $manager->CreateTable($self->table, file_get_contents($dir . 'comments.pingbacks.sql'));

    $posts = tposts::i();
    $posts->deleted = $self->postdeleted;
}

function tpingbacksUninstall($self) {
    tposts::unsub($self);
}