<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\posts;

function AjaxInstall($self) {
    litepubl::$urlmap->addget('/admin/ajaxposteditor.htm', get_class($self));
}

function AjaxUninstall($self) {
    turlmap::unsub($self);
}