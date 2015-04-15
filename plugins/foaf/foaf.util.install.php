<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tfoafutilInstall($self) {
  $cron = tcron::i();
  $cron->add('day', get_class($self), 'CheckFriendship', null);
}

function tfoafutilUninstall($self) {
  $cron = tcron::i();
  $cron->deleteclass(get_class($self));
}