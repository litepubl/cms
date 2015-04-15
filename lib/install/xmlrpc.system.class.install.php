<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCSystemInstall($self) {
  $caller = TXMLRPC::i();
  $caller->lock();
  
  $caller->add('system.listMethods' , 'listMethods', get_class($self));
  $caller->add('mt.listMethods' , 'supportedMethods', get_class($self));
  
  $caller->add('system.methodSignature' , 'methodSignature', get_class($self));
  $caller->add('system.methodHelp' , 'methodHelp', get_class($self));
  $caller->add('system.multicall' , 'multicall', get_class($self));
  $caller->add('system.methodExist' , 'methodExist', get_class($self));
  
  $caller->add('demo.sayHello', 'sayHello', get_class($self));
  $caller->add('demo.addTwoNumbers', 'addTwoNumbers',  get_class($self));
  $caller->add('sample.add', 'addTwoNumbers',  get_class($self));
  
  $caller->unlock();
}

?>