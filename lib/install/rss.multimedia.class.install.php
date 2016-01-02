<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function trssMultimediaInstall($self) {
  $urlmap = turlmap::i();
  $urlmap->lock();
  $urlmap->add('/rss/multimedia.xml', get_class($self), '');
  $urlmap->add('/rss/images.xml', get_class($self), 'image');
  $urlmap->add('/rss/audio.xml', get_class($self), 'audio');
  $urlmap->add('/rss/video.xml', get_class($self), 'video');
  $urlmap->unlock();
  
  $files = tfiles::i();
  $files->changed = $self->fileschanged;
  $self->save();
  
  $meta = tmetawidget::i();
  $meta->add('media', '/rss/multimedia.xml', tlocal::get('default', 'rssmedia'));
}

function trssMultimediaUninstall($self) {
  turlmap::unsub($self);
  $files = tfiles::i();
  $files->unbind($self);
  
  $meta = tmetawidget::i();
  $meta->delete('media');
}