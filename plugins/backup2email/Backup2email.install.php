<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\backup2email;

use litepubl\core\Cron;

function Backup2emailInstall($self)
{
    $cron = Cron::i();
    $self->idcron = $cron->add('week', get_class($self), 'send', null);
    $self->save();
}

function Backup2emailUninstall($self)
{
    Cron::i()->deleteclass($self);
}
