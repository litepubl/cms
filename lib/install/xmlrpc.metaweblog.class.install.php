<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCMetaWeblogInstall($self) {
  $caller = TXMLRPC::i();
  $caller->lock();
  // MetaWeblog API (with MT extensions to structs)
  $caller->add('metaWeblog.newPost', 'newPost', get_class($self));
  $caller->add('metaWeblog.editPost', 'editPost', get_class($self));
  $caller->add('metaWeblog.getPost', 'getPost', get_class($self));
  $caller->add('metaWeblog.getRecentPosts', 'getRecentPosts', get_class($self));
  $caller->add('metaWeblog.getCategories', 'getCategories', get_class($self));
  $caller->add('metaWeblog.newMediaObject', 'newMediaObject', get_class($self));
  
  // Aliases
  $caller->add('wp.getCategories',		'getCategories',	get_class($self));
  $caller->add('wp.uploadFile',		'newMediaObject',	get_class($self));
  
  //forward wordpress
  $caller->add('wp.newPage',	'wp_newPage', get_class($self));
  $caller->add('wp.editPage',	'wp_editPage', get_class($self));
  
  $caller->unlock();
}

?>