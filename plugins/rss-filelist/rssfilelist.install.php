<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function trssfilelistInstall($self) {
    $rss = trss::i();
    $rss->beforepost = $self->beforepost;

    litepubl::$urlmap->clearcache();
}

function trssfilelistUninstall($self) {
    $rss = trss::i();
    $rss->unbind($self);

    litepubl::$urlmap->clearcache();
}