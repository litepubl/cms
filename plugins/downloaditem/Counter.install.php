<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\downloaditem;

use litepubl\core\Cron;
use litepubl\pages\RobotsTxt;

function CounterInstall($self)
{
    $cron = Cron::i();
    $cron->add('hour', get_class($self), 'updatestat');

    $self->getApp()->router->addget('/downloaditem.htm', get_class($self));

    $robot = RobotsTxt::i();
    $robot->AddDisallow('/downloaditem.htm');
}

function CounterUninstall($self)
{
    $cron = Cron::i();
    $cron->deleteClass(get_class($self));

    $self->getApp()->router->unbind($self);
}
