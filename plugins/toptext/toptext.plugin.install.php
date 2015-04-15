<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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