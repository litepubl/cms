<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function tadminpasswordInstall($self) {
    litepubl::$urlmap->addget('/admin/password/', get_class($self));
}

function tadminpasswordUninstall($self) {
    turlmap::unsub($self);
}