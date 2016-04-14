<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\xmlrpc;

function ActionInstall($self) {
    $caller = Server::i();
    $caller->lock();
    $caller->add('litepublisher.action.send', 'send', get_class($self));
    $caller->add('litepublisher.action.confirm', 'confirm', get_class($self));
    $caller->unlock();
}

function ActionUninstall($self) {
    $caller = Server::i();
    $caller->deleteclass(get_class($self));
}