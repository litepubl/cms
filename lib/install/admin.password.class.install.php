<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminpasswordInstall($self) {
  litepublisher::$urlmap->addget('/admin/password/', get_class($self));
}

function tadminpasswordUninstall($self) {
  turlmap::unsub($self);
}