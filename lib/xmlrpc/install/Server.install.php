<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\xmlrpc;

function ServerInstall($self) {
    litepubl::$urlmap->add('/rpc.xml', get_class($self) , null);
    litepubl::$urlmap->add('/xmlrpc.php', get_class($self) , null);
}

function ServerUninstall($self) {
    turlmap::unsub($self);
}