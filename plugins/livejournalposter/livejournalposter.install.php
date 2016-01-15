<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

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