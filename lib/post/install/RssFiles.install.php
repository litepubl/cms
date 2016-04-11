<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\post;
use litepubl\core\litepubl;
use litepubl\widget\Meta as MetaWidget;

function RssFilesInstall($self) {
    litepubl::$router->add('/rss/multimedia.xml', get_class($self) , '');
    litepubl::$router->add('/rss/images.xml', get_class($self) , 'image');
    litepubl::$router->add('/rss/audio.xml', get_class($self) , 'audio');
    litepubl::$router->add('/rss/video.xml', get_class($self) , 'video');

    $files = Files::i();
    $files->changed = $self->fileschanged;
    $self->save();

    $meta = MetaWidget::i();
    $meta->add('media', '/rss/multimedia.xml', tlocal::get('default', 'rssmedia'));
}

function RssFilesUninstall($self) {
    litepubl::$router->unbind($self);
    $files = Files::i();
    $files->unbind($self);

    $meta = MetaWidget::i();
    $meta->delete('media');
}