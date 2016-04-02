<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function TXMLRPCInstall($self) {
  $urlmap = turlmap::i();
  $urlmap->lock();
  $urlmap->add('/rpc.xml', get_class($self) , null);
  $urlmap->add('/xmlrpc.php', get_class($self) , null);
  $urlmap->unlock();
}

function TXMLRPCUninstall($self) {
  turlmap::unsub($self);
}