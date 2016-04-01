<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tadminloginInstall($self) {
  litepublisher::$urlmap->addget('/admin/login/', get_class($self));
  litepublisher::$urlmap->add('/admin/logout/', get_class($self) , 'out', 'get');
}

function tadminloginUninstall($self) {
  turlmap::unsub($self);
}