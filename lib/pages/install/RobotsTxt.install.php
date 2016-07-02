<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\pages;

function RobotsTxtInstall($self)
{
    $self->lock();
    $self->idurl = $self->getApp()->router->add('/robots.txt', get_class($self), null);

    $self->add("#" . $self->getApp()->site->url . "/");
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

function RobotsTxtUninstall($self)
{
    $self->getApp()->router->unbind($self);
}
