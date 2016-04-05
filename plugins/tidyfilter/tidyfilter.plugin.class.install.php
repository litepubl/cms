<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

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