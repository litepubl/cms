<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\pages;
use litepubl\core\Cron;
use litepubl\widget\Meta;
use litepubl\view\Lang;

function SitemapInstall($self) {
    Cron::i()->addnightly(get_class($self) , 'Cron', null);

     $self->getApp()->router->add('/sitemap.xml', get_class($self) , 'xml');
     $self->getApp()->router->add('/sitemap.htm', get_class($self) , null);

    $robots = RobotsTxt::i();
    array_splice($robots->items, 1, 0, "Sitemap: " .  $self->getApp()->site->url . "/sitemap.xml");
    $robots->save();

    $self->add('/sitemap.htm', 4);
    $self->createfiles();

    $meta = Meta::i();
    $meta->add('sitemap', '/sitemap.htm', Lang::get('default', 'sitemap'));
}

function SitemapUninstall($self) {
     $self->getApp()->router->unbind($self);
    Cron::i()->deleteclass($self);
    $meta = Meta::i();
    $meta->delete('sitemap');
}