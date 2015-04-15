<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCLivejournalInstall($self) {
  $caller = TXMLRPC::i();
  $caller->lock();
  
  //Live journal api
  $caller->add('LJ.XMLRPC.login' , 'login', get_class($self));
  $caller->add('LJ.XMLRPC.getchallenge', 'getchallenge', get_class($self));
  $caller->add('LJ.XMLRPC.editevent', 'editevent', get_class($self));
  $caller->add('LJ.XMLRPC.postevent', 'postevent', get_class($self));
  //$caller->add('LJ.XMLRPC.checkfriends', 'checkfriends', get_class($self));
  $caller->unlock();
}

?>