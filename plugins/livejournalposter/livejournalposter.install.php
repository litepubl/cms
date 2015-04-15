<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tlivejournalposterInstall($self) {
  $posts = tposts::i();
  $posts->singlecron = $self->sendpost;
}

function tlivejournalposterUninstall($self) {
  tposts::unsub($self);
  if (dbversion) {
    //litepublisher::$db->table = 'postsmeta';
    //litepublisher::$db->delete("name = 'ljid'");
  }
}