<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function tadminsubscribersInstall($self) {
    litepubl::$urlmap->addget('/admin/subscribers/', get_class($self));
}

function tadminsubscribersUninstall($self) {
    turlmap::unsub($self);
}