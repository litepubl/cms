<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;
use litepubl\view\Lang;
use litepubl\view\Schemes;
use litepubl\view\Schema;
use litepubl\post\Posts;

function HomeInstall($self) {
    litepubl::$site->home = '/';
    $menus = Menus::i();
    $menus->lock();
    $self->lock();
    $self->url = '/';
    $self->title = Lang::i()->home;
    $self->IdSchema = Schemes::i()->add(Lang::get('adminmenus', 'home'));
    $schema = Schema::i($self->IdSchema);
    $schema->disableajax = true;
    $schema->save();

    $menus->idhome = $menus->add($self);
    $self->unlock();
    $menus->unlock();

    Posts::i()->addevent('changed', get_class($self) , 'postschanged');
}

function HomeUninstall($self) {
    litepubl::$router->unbind($self);
    Posts::unsub($self);

    $menus = Menus::i();
    $menus->lock();
    unset($menus->items[$menus->idhome]);
    $menus->sort();
    $menus->unlock();
}