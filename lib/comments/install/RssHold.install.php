<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\comments;

function RssHoldInstall($self) {
    $self->idurl = litepubl::$urlmap->add($self->url, get_class($self) , null, 'usernormal');

    $self->template = file_get_contents(dirname(dirname(__DIR__)) . '/install/templates/RssHold.tml');
    $self->save();

    Comments::i()->changed = $self->commentschanged;
}

function RssHoldUninstall($self) {
    turlmap::unsub($self);
    Comments::i()->unbind($self);
}