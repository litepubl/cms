<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCActionInstall($self) {
  $caller = TXMLRPC::i();
  $caller->lock();
  $caller->add('litepublisher.action.send', 'send', get_class($self));
  $caller->add('litepublisher.action.confirm', 'confirm', get_class($self));
  $caller->unlock();
}

function TXMLRPCActionUninstall($self) {
  $caller = TXMLRPC::i();
  $caller->deleteclass(get_class($self));
}

?>