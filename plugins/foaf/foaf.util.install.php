<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tfoafutilInstall($self) {
  $cron = tcron::i();
  $cron->add('day', get_class($self), 'CheckFriendship', null);
}

function tfoafutilUninstall($self) {
  $cron = tcron::i();
  $cron->deleteclass(get_class($self));
}