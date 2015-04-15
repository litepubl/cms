<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function appcache_manifestInstall($self) {
  $self->lock();
  $self->idurl = litepublisher::$urlmap->add($self->url, get_class($self), null);
  
  $self->add('$template.jsmerger_default');
  $self->add('$template.cssmerger_default');
  $self->unlock();
}

function appcache_manifestUninstall($self) {
  turlmap::unsub($self);
}