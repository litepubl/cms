<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function twidgetscacheInstall($self) {
  litepublisher::$urlmap->onclearcache = $self->onclearcache;
}

function twidgetscacheUninstall($self) {
  turlmap::unsub($self);
}