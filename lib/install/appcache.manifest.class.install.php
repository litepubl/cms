<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function appcache_manifestInstall($self) {
    $self->lock();
    turlmap::unsub($self);
    $self->idurl = litepubl::$urlmap->add($self->url, get_class($self) , null);

    $self->add('$template.jsmerger_default');
    $self->add('$template.cssmerger_default');
    $self->unlock();
}

function appcache_manifestUninstall($self) {
    turlmap::unsub($self);
}