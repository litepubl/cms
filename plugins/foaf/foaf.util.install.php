<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function tfoafutilInstall($self) {
    $cron = tcron::i();
    $cron->add('day', get_class($self) , 'CheckFriendship', null);
}

function tfoafutilUninstall($self) {
    $cron = tcron::i();
    $cron->deleteclass(get_class($self));
}