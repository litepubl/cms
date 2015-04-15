<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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