<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tjsonfilesInstall($self) {
  $json = tjsonserver::i();
  $json->lock();
  $json->addevent('files_getpost', get_class($self), 'files_getpost');
  $json->addevent('files_getpage', get_class($self), 'files_getpage');
  $json->addevent('files_setprops', get_class($self), 'files_setprops');
  $json->addevent('files_upload', get_class($self), 'files_upload');
  $json->unlock();
}

function tjsonfilesUninstall($self) {
  tjsonserver::i()->unbind($self);
}