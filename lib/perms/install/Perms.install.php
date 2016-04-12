<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\perms;
use litepubl\view\Lang;

function PermsInstall($self) {
    Lang::usefile('install');
    $lang = Lang::i('initgroups');

    $self->lock();
    $single = new tsinglepassword();
    $single->name = $lang->single;
    $self->add($single);
    $self->addclass($single);

    $pwd = new tpermpassword();
    $pwd->name = $lang->pwd;
    $self->add($pwd);
    $self->addclass($pwd);

    $groups = new tpermgroups();
    $groups->name = $lang->groups;
    $self->add($groups);
    $self->addclass($groups);

    $self->unlock();
}

function PermsUninstall($self) {
}