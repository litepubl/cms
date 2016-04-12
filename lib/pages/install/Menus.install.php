<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;

function MenusInstall($self) {
    @mkdir(litepubl::$paths->data . 'menus', 0777);
    if (get_class($self) != 'litepubl\pages\Menus') return;
    @chmod(litepubl::$paths->data . 'menus', 0777);

    litepubl::$classes->onrename = $self->classRenamed;
}

function MenusUninstall($self) {
    //rmdir(. 'menus');
    litepubl::$classes->unbind($self);
}

function MenusGetsitemap($self, $from, $count) {
    $result = array();
    foreach ($self->items as $id => $item) {
        if ($item['status'] == 'draft') continue;
        $result[] = array(
            'url' => $item['url'],
            'title' => $item['title'],
            'pages' => 1
        );
    }
    return $result;
}