<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function tbackup2emailInstall($self) {
    $cron = tcron::i();
    $self->idcron = $cron->add('week', get_class($self) , 'send', null);
    $self->save();
}

function tbackup2emailUninstall($self) {
    tcron::i()->deleteclass($self);
}