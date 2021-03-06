<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\admin\pages;

function LoginInstall($self)
{
    $self->getApp()->router->addget('/admin/login/', get_class($self));
    $self->getApp()->router->add('/admin/logout/', get_class($self), 'out', 'get');
}

function LoginUninstall($self)
{
    $self->getApp()->router->unbind($self);
}
