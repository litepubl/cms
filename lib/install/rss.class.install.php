<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function trssInstall($self) {
  litepublisher::$urlmap->add('/rss.xml', get_class($self), 'posts');
  $self->idcomments = litepublisher::$urlmap->add('/comments.xml', get_class($self), 'comments');
  $self->idpostcomments =   litepublisher::$urlmap->add('/comments/', get_class($self), null, 'tree');
  litepublisher::$urlmap->add('/rss/categories/', get_class($self), 'categories', 'tree');
  litepublisher::$urlmap->add('/rss/tags/', get_class($self), 'tags', 'tree');
  
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
  tcomments::i()->unbind($self);
  $meta = tmetawidget::i();
  $meta->lock();
  $meta->delete('rss');
  $meta->delete('comments');
  $meta->unlock();
}