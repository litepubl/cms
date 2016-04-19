<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\plugins\blackip;
use litepubl\comments\Manager;

function BlackIPInstall($self) {
    Manager::i()->oncreatestatus = $self->filter;
}

function BlackIPUninstall($self) {
    Manager::i()->unbind($self);
}