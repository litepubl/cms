<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpermsInstall($self) {
  tlocal::usefile('install');
  $lang = tlocal::i('initgroups');
  
  $self->lock();
  $single = new tsinglepassword();
  $single->name = $lang->single;
  $self->add($single);
  $self->addclass($single);
  
  $pwd = new tpermpassword();
  $pwd->name = $lang->pwd;
  $self->add($pwd);
  $self->addclass($pwd);
  
  $groups = new tpermgroups();
  $groups->name = $lang->groups;
  $self->add($groups);
  $self->addclass($groups);
  
  $self->unlock();
}

function tpermsUninstall($self) {
}