<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\xmlrpc;

function ServerInstall($self)
{
    $self->getApp()->router->add('/rpc.xml', get_class($self), null);
    $self->getApp()->router->add('/xmlrpc.php', get_class($self), null);
}

function ServerUninstall($self)
{
    $self->getApp()->router->unbind($self);
}
