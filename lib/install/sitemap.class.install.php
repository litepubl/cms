<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tsitemapInstall($self) {
  tcron::i()->addnightly(get_class($self) , 'Cron', null);

  litepubl::$urlmap->add('/sitemap.xml', get_class($self) , 'xml');
  litepubl::$urlmap->add('/sitemap.htm', get_class($self) , null);

  $robots = trobotstxt::i();
  array_splice($robots->items, 1, 0, "Sitemap: " . litepubl::$site->url . "/sitemap.xml");
  $robots->save();

  $self->add('/sitemap.htm', 4);
  $self->createfiles();

  $meta = tmetawidget::i();
  $meta->add('sitemap', '/sitemap.htm', tlocal::get('default', 'sitemap'));
}

function tsitemapUninstall($self) {
  turlmap::unsub($self);
  tcron::i()->deleteclass($self);
  $meta = tmetawidget::i();
  $meta->delete('sitemap');
}