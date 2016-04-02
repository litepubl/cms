<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function TXMLRPCPingbackInstall($self) {
  $Caller = TXMLRPC::i();
  $Caller->Add('pingback.ping', 'ping', get_class($self));
}