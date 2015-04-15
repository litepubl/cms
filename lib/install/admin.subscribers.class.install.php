<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminsubscribersInstall($self) {
  litepublisher::$urlmap->addget('/admin/subscribers/', get_class($self));
}

function tadminsubscribersUninstall($self) {
  turlmap::unsub($self);
}