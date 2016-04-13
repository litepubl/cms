<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\widget;

function CacheInstall($self) {
    litepubl::$urlmap->onclearcache = $self->onclearcache;
}

function CacheUninstall($self) {
    turlmap::unsub($self);
}