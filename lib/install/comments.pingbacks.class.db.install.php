<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpingbacksInstall($self) {
  $manager = tdbmanager ::i();
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
  $manager->CreateTable($self->table, file_get_contents($dir .'comments.pingbacks.sql'));
  
  $posts = tposts::i();
  $posts->deleted = $self->postdeleted;
}

function tpingbacksUninstall($self) {
  tposts::unsub($self);
}

?>