<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\tag;
use litepubl\post\Posts;
use litepubl\widget\Widgets;

function CommonInstall($self) {
    if ((__NAMESPACE__ . '\Common') == get_class($self)) return;

    $posts = Posts::i();
    $posts->lock();
    $posts->added = $self->postedited;
    $posts->edited = $self->postedited;
    $posts->deleted = $self->postdeleted;
    $posts->unlock();

    litepubl::$urlmap->add("/$self->PermalinkIndex/", get_class($self) , 0);

    $manager = $self->db->man;
    $dir = dirname(__file__) . '/sql/';
    $manager->createtable($self->table, file_get_contents($dir . 'tags.sql'));
    $manager->createtable($self->itemsposts->table, file_get_contents($dir . 'items.posts.sql'));
    $manager->createtable($self->contents->table, file_get_contents($dir . 'tags.content.sql'));
}

function CommonUninstall($self) {
    Posts::unsub($self);
    turlmap::unsub($self);

    $widgets = Widgets::i();
    $widgets->deleteclass(get_class($self));
}

function CommonGetsitemap($self, $from, $count) {
    $result = array();
    $self->loadall();
    foreach ($self->items as $id => $item) {
        $result[] = array(
            'url' => $item['url'],
            'title' => $item['title'],
            'pages' => (int)$item['lite'] ? 1 : ceil($item['itemscount'] / litepubl::$options->perpage)
        );
    }

    return $result;
}