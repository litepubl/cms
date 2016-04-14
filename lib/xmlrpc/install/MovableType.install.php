<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\xmlrpc;

function MovableTypeInstall($self) {
    $caller = Server::i();
    $caller->lock();

    // MovableType API
    $caller->add('mt.getCategoryList', 'getCategoryList', get_class($self));
    $caller->add('mt.getRecentPostTitles', 'getRecentPostTitles', get_class($self));
    $caller->add('mt.getPostCategories', 'getPostCategories', get_class($self));
    $caller->add('mt.setPostCategories', 'setPostCategories', get_class($self));
    $caller->add('mt.supportedTextFilters', 'supportedTextFilters', get_class($self));
    $caller->add('mt.getTrackbackPings', 'getTrackbackPings', get_class($self));
    $caller->add('mt.publishPost', 'publishPost', get_class($self));

    $caller->unlock();
}