<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;

function AppcacheInstall($self) {
    $self->lock();
    litepubl::$router->unbind($self);
    $self->idurl = litepubl::$urlmap->add($self->url, get_class($self) , null);

    $self->add('$template.jsmerger_default');
    $self->add('$template.cssmerger_default');
    $self->unlock();
}

function AppcacheUninstall($self) {
    litepubl::$router->unbind($self);
}