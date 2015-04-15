<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function ttidyfilterInstall($self) {
  if (!class_exists('tidy')) die('PHP tidy extension is required');
  $filter = tcontentfilter::i();
  $filter->lock();
  $filter->onaftersimple = $self->filter;
  $filter->onaftercomment = $self->filter;
  $filter->unlock();
}

function ttidyfilterUninstall($self) {
  $filter = tcontentfilter::i();
  $filter->unbind($self);
}