<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function tmanifestInstall($self) {
  $urlmap = turlmap::i();
  $urlmap->lock();
  $urlmap->add('/wlwmanifest.xml', get_class($self) , 'manifest');
  $urlmap->add('/rsd.xml', get_class($self) , 'rsd');
  $urlmap->Unlock();
}

function tmanifestUninstall($self) {
  turlmap::unsub($self);
}