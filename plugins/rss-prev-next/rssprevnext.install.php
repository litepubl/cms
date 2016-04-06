<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function TRSSPrevNextInstall($self) {
    $rss = trss::i();
    $rss->beforepost = $self->beforepost;

    $urlmap = turlmap::i();
    $urlmap->clearcache();
}

function TRSSPrevNextUninstall($self) {
    $rss = trss::i();
    $rss->unbind($self);

    $urlmap = turlmap::i();
    $urlmap->clearcache();
}