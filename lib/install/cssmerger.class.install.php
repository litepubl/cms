<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function tcssmergerInstall($self) {
    $self->lock();
    $self->items = array();
    $section = 'default';
    /*
    $list = tcssmerger_pretty_files($self);
    foreach ($list as $filename) {
    $self->add($section, $filename);
    }
    
    $items = tcssmerger_deprecated_files($self);
    foreach ($list as $filename) {
    $self->add($section, $filename);
    }
    */
    $self->add($section, '/js/litepubl/common/css/common.min.css');
    $self->add($section, '/js/litepubl/common/css/filelist.min.css');
    $self->add($section, '/js/litepubl/common/css/form.inline.min.css');

    $list = tcssmerger_bootstrap_files($self);
    foreach ($list as $filename) {
        $self->add($section, $filename);
    }

    $section = 'admin';
    //$self->add($section, '/js/jquery/ui/redmond/jquery-ui.min.css');
    $self->add($section, '/js/litepubl/admin/css/calendar.min.css');
    $self->add($section, '/js/litepubl/admin/css/fileman.min.css');
    $self->unlock();
    /*  moved to template install
    $template = ttemplate::i();
    $template->addtohead('<!--<link type="text/css" href="$site.files$template.cssmerger_default" rel="stylesheet" />-->');
    */
    tupdater::i()->onupdated = $self->save;
}

function tcssmergerUninstall($self) {
    tupdater::i()->unbind($self);
}

function tcssmerger_pretty_files($self) {
    return array(
        '/js/prettyphoto/css/prettyPhoto.css',
        '/js/litepubl/pretty/dialog.pretty.min.css',
    );
}

function tcssmerger_deprecated_files($self) {
    return array(
        '/js/litepubl/deprecated/css/align.min.css',
        '/js/litepubl/deprecated/css/button.min.css',
        '/js/litepubl/deprecated/css/table.min.css',
    );
}

function tcssmerger_bootstrap_files($self) {
    return array(
        '/js/litepubl/effects/css/homeimage.min.css',
        '/themes/default/css/logo.min.css'
    );
}