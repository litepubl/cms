<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function twidgetscacheInstall($self) {
  litepubl::$urlmap->onclearcache = $self->onclearcache;
}

function twidgetscacheUninstall($self) {
  turlmap::unsub($self);
}