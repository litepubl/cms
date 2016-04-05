<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function tadminboardInstall($self) {
    litepubl::$urlmap->add('/admin/', get_class($self) , null, 'normal');
}

function tadminboardUninstall($self) {
    turlmap::unsub($self);
}