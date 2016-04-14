<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\pages;

function PasswordInstall($self) {
    litepubl::$urlmap->addget('/admin/password/', get_class($self));
}

function PasswordUninstall($self) {
    turlmap::unsub($self);
}