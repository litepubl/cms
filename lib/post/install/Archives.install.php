<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\post;

function ArchivesInstall($self) {
    $posts = Posts::i();
    $posts->changed = $self->postschanged;
}

function ArchivesUninstall($self) {
    litepubl::$router->unbind($self);
    Posts::unsub($self);
    $widgets = twidgets::i();
    $widgets->deleteclass(get_class($self));
}

function tarchivesGetsitemap($self, $from, $count) {
    $result = array();
    foreach ($self->items as $date => $item) {
        $result[] = array(
            'url' => $item['url'],
            'title' => $item['title'],
            'pages' => 1
        );
    }
    return $result;
}