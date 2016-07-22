<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\xmlrpc;

function MovableTypeInstall($self)
{
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
