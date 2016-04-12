<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\post;
use litepubl\pages\Json;

function JsonFilesInstall($self) {
    $json = Json::i();
    $json->lock();
    $json->addevent('files_getpost', get_class($self) , 'files_getpost');
    $json->addevent('files_getpage', get_class($self) , 'files_getpage');
    $json->addevent('files_setprops', get_class($self) , 'files_setprops');
    $json->addevent('files_upload', get_class($self) , 'files_upload');
    $json->unlock();
}

function JsonFilesUninstall($self) {
    Json::i()->unbind($self);
}