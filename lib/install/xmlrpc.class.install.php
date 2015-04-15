<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCInstall($self) {
  $urlmap = turlmap::i();
  $urlmap->lock();
  $urlmap->add('/rpc.xml', get_class($self), null);
  $urlmap->add('/xmlrpc.php', get_class($self), null);
  $urlmap->unlock();
}

function TXMLRPCUninstall($self) {
  turlmap::unsub($self);
}
?>