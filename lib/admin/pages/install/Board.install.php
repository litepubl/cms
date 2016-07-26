<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\admin\pages;

function BoardInstall($self)
{
    $self->getApp()->router->add('/admin/', get_class($self), null, 'normal');
}

function BoardUninstall($self)
{
    $self->getApp()->router->unbind($self);
}
