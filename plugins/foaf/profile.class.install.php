<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\MainView;

function tprofileInstall($self) {
     $self->getApp()->router->add($self->url, get_class($self) , null);

    $sitemap = tsitemap::i();
    $sitemap->add($self->url, 7);

    $template = MainView::i();
    $template->addtohead('	<link rel="author profile" title="Profile" href="$site.url/profile.htm" />');
}

function tprofileUninstall($self) {
     $self->getApp()->router->unbind($self);

    $sitemap = tsitemap::i();
    $sitemap->delete('/profile.htm');

    $template = MainView::i();
    $template->deletefromhead('	<link rel="author profile" title="Profile" href="$site.url/profile.htm" />');
}