<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tpostcontentpluginInstall($self) {
    $posts = tposts::i();
    $posts->lock();
    $posts->beforecontent = $self->beforecontent;
    $posts->aftercontent = $self->aftercontent;
    $posts->unlock();
}

function tpostcontentpluginUninstall($self) {
    tposts::unsub($self);
}