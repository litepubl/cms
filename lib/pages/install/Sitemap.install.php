<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;
use litepubl\core\Cron;
use litepubl\widget\Meta;

function SitemapInstall($self) {
    Cron::i()->addnightly(get_class($self) , 'Cron', null);

    litepubl::$urlmap->add('/sitemap.xml', get_class($self) , 'xml');
    litepubl::$urlmap->add('/sitemap.htm', get_class($self) , null);

    $robots = RobotsTxt::i();
    array_splice($robots->items, 1, 0, "Sitemap: " . litepubl::$site->url . "/sitemap.xml");
    $robots->save();

    $self->add('/sitemap.htm', 4);
    $self->createfiles();

    $meta = Meta::i();
    $meta->add('sitemap', '/sitemap.htm', tlocal::get('default', 'sitemap'));
}

function SitemapUninstall($self) {
    litepubl::$router->unbind($self);
    Cron::i()->deleteclass($self);
    $meta = Meta::i();
    $meta->delete('sitemap');
}