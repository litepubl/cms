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

function PasswordInstall($self)
{
    $self->getApp()->router->addget('/admin/password/', get_class($self));
}

function PasswordUninstall($self)
{
    $self->getApp()->router->unbind($self);
}
