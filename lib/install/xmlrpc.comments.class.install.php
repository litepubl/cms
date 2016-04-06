<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function TXMLRPCCommentsInstall($self) {
    $caller = TXMLRPC::i();
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
    if (dbversion) {
        $caller->add('wp.getComment', 'wpgetComment', get_class($self));
        $caller->add('wp.getComments', 'wpgetComments', get_class($self));
        $caller->add('wp.deleteComment', 'wpdeleteComment', get_class($self));
        $caller->add('wp.editComment', 'wpeditComment', get_class($self));
        $caller->add('wp.getCommentStatusList', '	', get_class($self));
    }

    $caller->unlock();
}