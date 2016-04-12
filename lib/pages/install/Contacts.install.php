<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;
use litepubl\view\Lang;
use litepubl\view\Theme;

function ContactsInstall($self) {
    $ini = parse_ini_file(dirname(__file__) . '/templates/contactform.ini');
    Lang::usefile('install');
    $lang = Lang::i('contactform');
    $theme = Theme::i();

    $self->title = $lang->title;
    $self->subject = $lang->subject;
    $self->order = 10;
    $self->success = $theme->parse($ini['success']);
    $self->errmesg = $theme->parse($ini['errmesg']);
    $self->content = $theme->parse($ini['form']);

    $menus = Menus::i();
    $menus->add($self);
}

function ContactsUninstall($self) {
    $menus = Menus::i();
    $menus->delete($self->id);
}