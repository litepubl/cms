<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tsubscribersInstall($self) {
  $dbmanager = tdbmanager::i();
  $dbmanager->CreateTable($self->table, file_get_contents(dirname(__file__) . '/sql/items.posts.sql'));

  $self->fromemail = 'litepublisher@' . $_SERVER['HTTP_HOST'];
  $self->save();

  $posts = tposts::i();
  $posts->added = $self->postadded;
  $posts->deleted = $self->deletepost;

  $comments = tcomments::i();
  $comments->lock();
  $comments->added = $self->sendmail;
  $comments->onapproved = $self->sendmail;
  $comments->unlock();

  tusers::i()->deleted = $self->deleteitem;
}

function tsubscribersUninstall($self) {
  tcomments::i()->unbind($self);
  tusers::i()->unbind($self);
  tposts::i()->unbind($self);
}