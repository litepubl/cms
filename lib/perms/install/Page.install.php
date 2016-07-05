<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 */

namespace litepubl\perms;

use litepubl\pages\RobotsTxt;

function PageInstall($self)
{
    RobotsTxt::i()->AddDisallow($self->url);
    $self->getApp()->router->delete($self->url);
    $self->getApp()->router->addget($self->url, get_class($self));
}

function PageUninstall($self)
{
    $self->getApp()->router->umbind($self);
}
