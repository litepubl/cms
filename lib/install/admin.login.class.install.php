<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminloginInstall($self) {
  litepublisher::$urlmap->addget('/admin/login/', get_class($self));
  litepublisher::$urlmap->add('/admin/logout/', get_class($self), 'out', 'get');
}

function tadminloginUninstall($self) {
  turlmap::unsub($self);
}