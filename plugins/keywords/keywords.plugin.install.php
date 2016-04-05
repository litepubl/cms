<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function tkeywordspluginInstall($self) {
    @mkdir(litepubl::$paths->data . 'keywords', 0777);
    @chmod(litepubl::$paths->data . 'keywords', 0777);

    $item = litepubl::$classes->items[get_class($self) ];
    litepubl::$classes->add('tkeywordswidget', 'keywords.widget.php', $item[1]);

    $widget = tkeywordswidget::i();
    $widgets = twidgets::i();
    $widgets->lock();
    $id = $widgets->add($widget);
    $sidebars = tsidebars::i();
    $sidebars->insert($id, false, 1, -1);
    $widgets->unlock();

    $urlmap = turlmap::i();
    $urlmap->lock();
    $urlmap->afterrequest = $self->parseref;
    $urlmap->deleted = $self->urldeleted;
    $urlmap->unlock();
}

function tkeywordspluginUninstall($self) {
    turlmap::unsub($self);
    $widgets = twidgets::i();
    $widgets->deleteclass('tkeywordswidget');
    litepubl::$classes->delete('tkeywordswidget');
    //TFiler::DeleteFiles(litepubl::$paths->data . 'keywords' . DIRECTORY_SEPARATOR  , true);
    
}