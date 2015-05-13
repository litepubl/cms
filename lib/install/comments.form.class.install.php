<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tcommentformInstall($self) {
  $url= '/send-comment.php';
  
  litepublisher::$urlmap->Add($url, get_class($self), null);
}

function tcommentformUninstall($self) {
  turlmap::unsub($self);
}

function tkeptcommentsInstall($self) {
  if (dbversion) {
    $manager = tdbmanager ::i();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'comments.kept.sql'));
  }
}