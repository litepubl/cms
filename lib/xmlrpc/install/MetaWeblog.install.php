<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\xmlrpc;

function MetaWeblogInstall($self)
{
    $caller = Server::i();
    $caller->lock();
    // MetaWeblog API (with MT extensions to structs)
    $caller->add('metaWeblog.newPost', 'newPost', get_class($self));
    $caller->add('metaWeblog.editPost', 'editPost', get_class($self));
    $caller->add('metaWeblog.getPost', 'getPost', get_class($self));
    $caller->add('metaWeblog.getRecentPosts', 'getRecentPosts', get_class($self));
    $caller->add('metaWeblog.getCategories', 'getCategories', get_class($self));
    $caller->add('metaWeblog.newMediaObject', 'newMediaObject', get_class($self));

    // Aliases
    $caller->add('wp.getCategories', 'getCategories', get_class($self));
    $caller->add('wp.uploadFile', 'newMediaObject', get_class($self));

    //forward wordpress
    $caller->add('wp.newPage', 'wp_newPage', get_class($self));
    $caller->add('wp.editPage', 'wp_editPage', get_class($self));

    $caller->unlock();
}
