<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\view;

function CssInstall($self) {
    $self->lock();
    $self->items = array();
    $section = 'default';
    $self->add($section, '/js/litepubl/common/css/common.min.css');
    $self->add($section, '/js/litepubl/common/css/filelist.min.css');
    $self->add($section, '/js/litepubl/common/css/form.inline.min.css');

    $list = Css_bootstrap_files($self);
    foreach ($list as $filename) {
        $self->add($section, $filename);
    }

    $section = 'admin';
    $self->add($section, '/js/litepubl/admin/css/calendar.min.css');
    $self->add($section, '/js/litepubl/admin/css/fileman.min.css');
    $self->unlock();
    //tupdater::i()->onupdated = $self->save;
}

function CssUninstall($self) {
    //tupdater::i()->unbind($self);
}

function Css_pretty_files($self) {
    return array(
        '/js/prettyphoto/css/prettyPhoto.css',
        '/js/litepubl/pretty/dialog.pretty.min.css',
    );
}

function Css_deprecated_files($self) {
    return array(
        '/js/litepubl/deprecated/css/align.min.css',
        '/js/litepubl/deprecated/css/button.min.css',
        '/js/litepubl/deprecated/css/table.min.css',
    );
}

function Css_bootstrap_files($self) {
    return array(
        '/js/litepubl/effects/css/homeimage.min.css',
        '/themes/default/css/logo.min.css'
    );
}