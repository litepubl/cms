<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\pages;

function RegUserInstall($self) {
    litepubl::$urlmap->addget('/admin/reguser/', get_class($self));
}

function RegUserUninstall($self) {
    turlmap::unsub($self);
}