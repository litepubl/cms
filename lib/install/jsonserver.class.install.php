<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tjsonserverInstall($self) {
  litepublisher::$urlmap->addget($self->url, get_class($self));
}

function tjsonserverUninstall($self) {
  turlmap::unsub($self);
}