<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tdownloaditemcounterInstall($self) {
  $cron = tcron::i();
  $cron->add('hour', get_class($self), 'updatestat');
  
  litepublisher::$urlmap->addget('/downloaditem.htm', get_class($self));
  
  $robot = trobotstxt::i();
  $robot->AddDisallow('/downloaditem.htm');
}

function tdownloaditemcounterUninstall($self) {
  $cron = tcron::i();
  $cron->deleteclass(get_class($self));
  
  turlmap::unsub($self);
}