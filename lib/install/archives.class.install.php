<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tarchivesInstall($self) {
  $posts = tposts::i();
  $posts->changed = $self->postschanged;
  if (!dbversion) $self->postschanged();
}

function tarchivesUninstall($self) {
  turlmap::unsub($self);
  tposts::unsub($self);
  $widgets = twidgets::i();
  $widgets->deleteclass(get_class($self));
}
function tarchivesGetsitemap($self, $from, $count) {
  $result = array();
  foreach ($self->items as $date => $item) {
    $result[] = array(
    'url' => $item['url'],
    'title' => $item['title'],
    'pages' => 1
    );
  }
  return $result;
}