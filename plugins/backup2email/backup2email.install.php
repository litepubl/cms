<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tbackup2emailInstall($self) {
  $cron = tcron::i();
  $self->idcron = $cron->add('week', get_class($self), 'send', null);
  $self->save();
}

function tbackup2emailUninstall($self) {
  tcron::i()->deleteclass($self);
}