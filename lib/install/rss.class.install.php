<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function trssInstall($self) {
  litepublisher::$urlmap->add('/rss.xml', get_class($self) , 'posts');
  $self->idcomments = litepublisher::$urlmap->add('/comments.xml', get_class($self) , 'comments');
  $self->idpostcomments = litepublisher::$urlmap->add('/comments/', get_class($self) , null, 'begin');
  litepublisher::$urlmap->add('/rss/categories/', get_class($self) , 'categories', 'begin');
  litepublisher::$urlmap->add('/rss/tags/', get_class($self) , 'tags', 'begin');

  tcomments::i()->changed = $self->commentschanged;

  $self->save();

  $meta = tmetawidget::i();
  $meta->lock();
  $meta->add('rss', '/rss.xml', tlocal::get('default', 'rss'));
  $meta->add('comments', '/comments.xml', tlocal::get('default', 'rsscomments'));
  $meta->unlock();
}

function trssUninstall($self) {
  turlmap::unsub($self);
  litepublisher::$urlmap->updatefilter();
  tcomments::i()->unbind($self);
  $meta = tmetawidget::i();
  $meta->lock();
  $meta->delete('rss');
  $meta->delete('comments');
  $meta->unlock();
}