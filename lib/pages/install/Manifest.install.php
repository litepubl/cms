<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\pages;

function ManifestInstall($self)
{
    $self->getApp()->router->add('/wlwmanifest.xml', get_class($self), 'manifest');
    $self->getApp()->router->add('/rsd.xml', get_class($self), 'rsd');
}

function ManifestUninstall($self)
{
    $self->getApp()->router->unbind($self);
}
