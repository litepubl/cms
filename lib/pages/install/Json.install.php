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

function JsonInstall($self)
{
    $self->getApp()->router->addget($self->url, get_class($self));
}

function JsonUninstall($self)
{
    $self->getApp()->router->unbind($self);
}
