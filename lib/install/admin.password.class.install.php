<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tadminpasswordInstall($self) {
  litepublisher::$urlmap->addget('/admin/password/', get_class($self));
}

function tadminpasswordUninstall($self) {
  turlmap::unsub($self);
}