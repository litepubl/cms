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

function BloggerInstall($self)
{
    $caller = Server::i();
    $caller->lock();

    // Blogger API
    $caller->add('blogger.getUsersBlogs', 'getUsersBlogs', get_class($self));
    $caller->add('blogger.getUserInfo', 'getUserInfo', get_class($self));
    $caller->add('blogger.getPost', 'getPost', get_class($self));
    $caller->add('blogger.getRecentPosts', 'getRecentPosts', get_class($self));
    $caller->add('blogger.newPost', 'newPost', get_class($self));
    $caller->add('blogger.editPost', 'editPost', get_class($self));
    $caller->add('blogger.deletePost', 'deletePost', get_class($self));
    $caller->add('blogger.getTemplate', 'getTemplate', get_class($self));
    $caller->add('blogger.setTemplate', 'setTemplate', get_class($self));

    // MetaWeblog API aliases for Blogger API
    // see http://www.xmlrpc.com/stories/storyReader$2460
    $caller->add('metaWeblog.deletePost', 'deletePost', get_class($self));
    $caller->add('metaWeblog.getUsersBlogs', 'getUsersBlogs', get_class($self));

    $caller->unlock();
}
