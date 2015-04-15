<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpollsmanInstall($self) {
  tcontentfilter::i()->beforefilter = $self->filter;
  tcron::i()->addnightly(get_class($self), 'optimize', null);
}

function tpollsmanUninstall($self) {
  tcontentfilter::i()->unbind($self);
  tcron::i()->deleteclass(get_class($self));
  
  $posts = tposts::i();
  $posts->syncmeta = false;
  $posts->unbind($self);
  
  litepublisher::$db->table = 'postsmeta';
  litepublisher::$db->delete("name = 'poll'");
}