<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\pages;

function BoardInstall($self) {
    litepubl::$urlmap->add('/admin/', get_class($self) , null, 'normal');
}

function BoardUninstall($self) {
    turlmap::unsub($self);
}