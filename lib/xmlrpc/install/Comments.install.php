<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\xmlrpc;

function CommentsInstall($self)
{
    $caller = Server::i();
    $caller->lock();

    $caller->add('litepublisher.deletecomment', 'delete', get_class($self));
    $caller->add('litepublisher.comments.setstatus', 'setstatus', get_class($self));
    $caller->add('litepublisher.comments.add', 'add', get_class($self));
    $caller->add('litepublisher.comments.edit', 'edit', get_class($self));
    $caller->add('litepublisher.comments.reply', 'reply', get_class($self));
    $caller->add('litepublisher.comments.get', 'getcomment', get_class($self));
    $caller->add('litepublisher.comments.getrecent', 'getrecent', get_class($self));
    $caller->add('litepublisher.moderate', 'moderate', get_class($self));

    //wordpress api
    $caller->add('wp.getCommentCount', 'wpgetCommentCount', get_class($self));
    $caller->add('wp.newComment', 'wpnewComment', get_class($self));
    $caller->add('wp.getComment', 'wpgetComment', get_class($self));
    $caller->add('wp.getComments', 'wpgetComments', get_class($self));
    $caller->add('wp.deleteComment', 'wpdeleteComment', get_class($self));
    $caller->add('wp.editComment', 'wpeditComment', get_class($self));
    $caller->add('wp.getCommentStatusList', '	', get_class($self));
    $caller->unlock();
}
