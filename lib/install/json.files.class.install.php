<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
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