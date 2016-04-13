<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\comments;

function FormInstall($self) {
    $url = '/send-comment.php';

    litepubl::$urlmap->Add($url, get_class($self) , null);
}

function FormUninstall($self) {
    turlmap::unsub($self);
}