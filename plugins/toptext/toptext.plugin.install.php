<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function ttoptextinstall($self) {
  $filter = tcontentfilter::i();
  $filter->lock();
  $filter->beforecontent = $self->beforecontent;
  $filter->aftercontent = $self->aftercontent;
  $filter->unlock();
}

function ttoptextuninstall($self) {
  $filter = tcontentfilter::i();
  $filter->unbind($self);
}