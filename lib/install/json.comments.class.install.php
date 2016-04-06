<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tjsoncommentsInstall($self) {
    $json = tjsonserver::i();
    $json->lock();
    $json->addevent('comment_delete', get_class($self) , 'comment_delete');
    $json->addevent('comment_setstatus', get_class($self) , 'comment_setstatus');
    $json->addevent('comment_edit', get_class($self) , 'comment_edit');
    $json->addevent('comment_getraw', get_class($self) , 'comment_getraw');
    $json->addevent('comments_get_hold', get_class($self) , 'comments_get_hold');
    $json->addevent('comment_add', get_class($self) , 'comment_add');
    $json->addevent('comment_confirm', get_class($self) , 'comment_confirm');
    $json->addevent('comments_get_logged', get_class($self) , 'comments_get_logged');
    $json->unlock();
}

function tjsoncommentsUninstall($self) {
    tjsonserver::i()->unbind($self);
}