<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\xmlrpc;

function PingbackInstall($self) {
    $Caller = Server::i();
    $Caller->Add('pingback.ping', 'ping', get_class($self));
}