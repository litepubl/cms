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

function SystemInstall($self)
{
    $caller = Server::i();
    $caller->lock();

    $caller->add('system.listMethods', 'listMethods', get_class($self));
    $caller->add('mt.listMethods', 'supportedMethods', get_class($self));

    $caller->add('system.methodSignature', 'methodSignature', get_class($self));
    $caller->add('system.methodHelp', 'methodHelp', get_class($self));
    $caller->add('system.multicall', 'multicall', get_class($self));
    $caller->add('system.methodExist', 'methodExist', get_class($self));

    $caller->add('demo.sayHello', 'sayHello', get_class($self));
    $caller->add('demo.addTwoNumbers', 'addTwoNumbers', get_class($self));
    $caller->add('sample.add', 'addTwoNumbers', get_class($self));

    $caller->unlock();
}
