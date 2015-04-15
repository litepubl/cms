<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tmanifestInstall($self) {
  $urlmap = turlmap::i();
  $urlmap->lock();
  $urlmap->add('/wlwmanifest.xml', get_class($self), 'manifest');
  $urlmap->add('/rsd.xml', get_class($self), 'rsd');
  $urlmap->Unlock();
}

function tmanifestUninstall($self) {
  turlmap::unsub($self);
}