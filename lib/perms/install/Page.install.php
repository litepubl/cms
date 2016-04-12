<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\perms;
use litepubl\pages\RobotsTxt;

function PageInstall($self) {
    RobotsTxt::i()->AddDisallow($self->url);
    litepubl::$urlmap->delete($self->url);
    litepubl::$urlmap->addget($self->url, get_class($self));
}

function PageUninstall($self) {
    litepubl::$urlmap->umbind($self);
}