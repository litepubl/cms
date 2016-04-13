<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function tajaxposteditorInstall($self) {
    litepubl::$urlmap->addget('/admin/ajaxposteditor.htm', get_class($self));
}

function tajaxposteditorUninstall($self) {
    turlmap::unsub($self);
}