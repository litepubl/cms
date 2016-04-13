<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\pages;

function LoginInstall($self) {
    litepubl::$urlmap->addget('/admin/login/', get_class($self));
    litepubl::$urlmap->add('/admin/logout/', get_class($self) , 'out', 'get');
}

function LoginUninstall($self) {
    turlmap::unsub($self);
}