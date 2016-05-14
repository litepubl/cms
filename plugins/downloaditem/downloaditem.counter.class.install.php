<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

function tdownloaditemcounterInstall($self)
{
    $cron = tcron::i();
    $cron->add('hour', get_class($self) , 'updatestat');

    $self->getApp()->router->addget('/downloaditem.htm', get_class($self));

    $robot = trobotstxt::i();
    $robot->AddDisallow('/downloaditem.htm');
}

function tdownloaditemcounterUninstall($self)
{
    $cron = tcron::i();
    $cron->deleteclass(get_class($self));

    $self->getApp()->router->unbind($self);
}

