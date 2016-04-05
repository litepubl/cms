<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function tcommentformInstall($self) {
    $url = '/send-comment.php';

    litepubl::$urlmap->Add($url, get_class($self) , null);
}

function tcommentformUninstall($self) {
    turlmap::unsub($self);
}