<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tmetapostInstall($self) {
  if (dbversion) {
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager = tdbmanager ::i();
    $manager->CreateTable($self->table, file_get_contents($dir .'post.meta.sql'));
  }
}

function tmetapostUninstall($self) {
}

?>