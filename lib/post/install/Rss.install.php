<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\post;
use litepubl\comments\Comments;
use litepubl\widget\Meta as MetaWidget;

function RssInstall($self) {
    litepubl::$router->add('/rss.xml', get_class($self) , 'posts');
    $self->idcomments = litepubl::$router->add('/comments.xml', get_class($self) , 'comments');
    $self->idpostcomments = litepubl::$router->add('/comments/', get_class($self) , null, 'begin');
    litepubl::$router->add('/rss/categories/', get_class($self) , 'categories', 'begin');
    litepubl::$router->add('/rss/tags/', get_class($self) , 'tags', 'begin');

    Comments::i()->changed = $self->commentschanged;

    $self->save();

    $meta = MetaWidget::i();
    $meta->lock();
    $meta->add('rss', '/rss.xml', Lang::get('default', 'rss'));
    $meta->add('comments', '/comments.xml', Lang::get('default', 'rsscomments'));
    $meta->unlock();
}

function RssUninstall($self) {
    litepubl::$router->unbind($self);
    litepubl::$router->updatefilter();
    Comments::i()->unbind($self);
    $meta  = MetaWidget::i();
    $meta->lock();
    $meta->delete('rss');
    $meta->delete('comments');
    $meta->unlock();
}