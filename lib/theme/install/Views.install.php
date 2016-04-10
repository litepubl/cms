<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\theme;
use litepubl\widget\Widgets;

function ViewsInstall($self) {
    $widgets = Widgets::i();
    $widgets->deleted = $self->widgetdeleted;

    $self->lock();
    $lang = Lang::admin('names');
    $default = $self->add($lang->default);
    $def = View::i($default);
    $def->sidebars = array(
        array() ,
        array() ,
        array()
    );

    $idadmin = $self->add($lang->adminpanel);
    $admin = View::i($idadmin);
    $admin->menuclass = 'litepubl\admin\menus';

    $self->defaults = array(
        'post' => $default,
        'menu' => $default,
        'category' => $default,
        'tag' => $default,
        'admin' => $idadmin
    );

    $self->unlock();
}