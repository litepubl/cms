<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\pages;

function AppcacheInstall($self)
{
    $self->lock();
    $self->getApp()->router->unbind($self);
    $self->idurl = $self->getApp()->router->add($self->url, get_class($self), null);

    $self->add('$template.jsmerger_default');
    $self->add('$template.cssmerger_default');
    $self->unlock();
}

function AppcacheUninstall($self)
{
    $self->getApp()->router->unbind($self);
}
