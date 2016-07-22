<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\xmlrpc;

function LivejournalInstall($self)
{
    $caller = Server::i();
    $caller->lock();

    //Live journal api
    $caller->add('LJ.XMLRPC.login', 'login', get_class($self));
    $caller->add('LJ.XMLRPC.getchallenge', 'getchallenge', get_class($self));
    $caller->add('LJ.XMLRPC.editevent', 'editevent', get_class($self));
    $caller->add('LJ.XMLRPC.postevent', 'postevent', get_class($self));
    //$caller->add('LJ.XMLRPC.checkfriends', 'checkfriends', get_class($self));
    $caller->unlock();
}
