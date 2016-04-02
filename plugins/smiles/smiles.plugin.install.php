<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl\plugins;
use litepubl;

function tsmilesInstall($self) {
  $filter = tcontentfilter::i();
  $filter->lock();
  $filter->onsimplefilter = $self->filter;
  $filter->oncomment = $self->filter;
  $filter->unlock();

  tposts::i()->addrevision();
}

function tsmilesUninstall($self) {
  tcontentfilter::i()->unbind($self);
  tposts::i()->addrevision();
}