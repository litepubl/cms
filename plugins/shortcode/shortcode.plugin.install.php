<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl\plugins;
use litepubl;

function tshortcodeInstall($self) {
  $filter = tcontentfilter::i();
  $filter->lock();
  $filter->beforefilter = $self->filter;
  $filter->oncomment = $self->filter;
  $filter->unlock();
}

function tshortcodeUninstall($self) {
  tcontentfilter::i()->unbind($self);
}