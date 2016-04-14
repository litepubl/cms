<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\comments;
use litepubl\core\users;
use litepubl\post\Posts;

function SubscribersInstall($self) {
    $dbmanager = $self->db->man;
    $dbmanager->CreateTable($self->table, file_get_contents(dirname(dirname(__DIR__)) . '/core/install/sql/ItemsPosts.sql'));

    $self->fromemail = 'litepublisher@' . $_SERVER['HTTP_HOST'];
    $self->save();

    $posts = Posts::i();
    $posts->added = $self->postadded;
    $posts->deleted = $self->deletepost;

    $comments = Comments::i();
    $comments->lock();
    $comments->added = $self->sendmail;
    $comments->onapproved = $self->sendmail;
    $comments->unlock();

    Users::i()->deleted = $self->deleteitem;
}

function SubscribersUninstall($self) {
    Comments::i()->unbind($self);
    Users::i()->unbind($self);
    Posts::i()->unbind($self);
}