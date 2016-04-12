<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;

function RobotsTxtInstall($self) {
    $self->lock();
    $self->idurl = litepubl::$router->add('/robots.txt', get_class($self) , null);

    $self->add("#" . litepubl::$site->url . "/");
    $self->add('User-agent: *');
    //$self->AddDisallow('/rss.xml');
    //$self->AddDisallow('/comments.xml');
    //$self->AddDisallow('/comments/');
    $self->AddDisallow('/admin/');
    $self->AddDisallow('/admin/');
    $self->AddDisallow('/wlwmanifest.xml');
    $self->AddDisallow('/rsd.xml');
    $self->unlock();
}

function RobotsTxtUninstall($self) {
    litepubl::$router->unbind($self);
}