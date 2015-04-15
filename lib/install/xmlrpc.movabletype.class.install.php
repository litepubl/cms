<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCMovableTypeInstall($self) {
  $caller = TXMLRPC::i();
  $caller->lock();
  
  // MovableType API
  $caller->add('mt.getCategoryList' , 'getCategoryList', get_class($self));
  $caller->add('mt.getRecentPostTitles' , 'getRecentPostTitles', get_class($self));
  $caller->add('mt.getPostCategories' , 'getPostCategories', get_class($self));
  $caller->add('mt.setPostCategories' , 'setPostCategories', get_class($self));
  $caller->add('mt.supportedTextFilters' , 'supportedTextFilters', get_class($self));
  $caller->add('mt.getTrackbackPings' , 'getTrackbackPings', get_class($self));
  $caller->add('mt.publishPost' , 'publishPost', get_class($self));
  
  $caller->unlock();
}

?>