<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function trssholdcommentsInstall($self) {
  $self->idurl = litepublisher::$urlmap->add($self->url, get_class($self) , null, 'usernormal');

  $html = tadminhtml::i();
  $html->section = 'installation';
  $self->template = $html->rsstemplate;
  $self->save();

  tcomments::i()->changed = $self->commentschanged;
}

function trssholdcommentsUninstall($self) {
  turlmap::unsub($self);
  tcomments::i()->unbind($self);
}