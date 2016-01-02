<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tbackup2emailInstall($self) {
  $cron = tcron::i();
  $self->idcron = $cron->add('week', get_class($self), 'send', null);
  $self->save();
}

function tbackup2emailUninstall($self) {
  tcron::i()->deleteclass($self);
}