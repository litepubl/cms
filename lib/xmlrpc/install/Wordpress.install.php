<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 */

namespace litepubl\xmlrpc;

function WordpressInstall($self)
{
    $caller = Server::i();
    $caller->lock();
    // WordPress API
    $caller->add('wp.getPage', 'wp_getPage', get_class($self));
    $caller->add('wp.getPages', 'wp_getPages', get_class($self));
    $caller->add('wp.deletePage', 'wp_deletePage', get_class($self));
    $caller->add('wp.getPageList', 'wp_getPageList', get_class($self));
    $caller->add('wp.newCategory', 'wp_newCategory', get_class($self));
    $caller->add('wp.deleteCategory ', 'deleteCategory ', get_class($self));
    $caller->add('wp.getTags', 'getTags', get_class($self));

    $caller->unlock();
}
