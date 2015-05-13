<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tadminboardInstall($self) {
  litepublisher::$urlmap->add('/admin/', get_class($self), null, 'normal');
}

function tadminboardUninstall($self) {
  turlmap::unsub($self);
}