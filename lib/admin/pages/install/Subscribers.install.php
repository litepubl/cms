<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\pages;

function SubscribersInstall($self) {
    litepubl::$urlmap->addget('/admin/subscribers/', get_class($self));
}

function SubscribersUninstall($self) {
    turlmap::unsub($self);
}