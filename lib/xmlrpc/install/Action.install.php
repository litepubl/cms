<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\xmlrpc;

function ActionInstall($self)
{
    $caller = Server::i();
    $caller->lock();
    $caller->add('litepublisher.action.send', 'send', get_class($self));
    $caller->add('litepublisher.action.confirm', 'confirm', get_class($self));
    $caller->unlock();
}

function ActionUninstall($self)
{
    $caller = Server::i();
    $caller->deleteclass(get_class($self));
}
