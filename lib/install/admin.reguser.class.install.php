<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tadminreguserInstall($self) {
  litepublisher::$urlmap->addget('/admin/reguser/', get_class($self));
}

function tadminreguserUninstall($self) {
  turlmap::unsub($self);
}