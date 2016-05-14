<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\pages;

function ManifestInstall($self)
{
    $self->getApp()->router->add('/wlwmanifest.xml', get_class($self) , 'manifest');
    $self->getApp()->router->add('/rsd.xml', get_class($self) , 'rsd');
}

function ManifestUninstall($self)
{
    $self->getApp()->router->unbind($self);
}

