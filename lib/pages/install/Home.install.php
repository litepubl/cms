<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\pages;
use litepubl\view\Lang;
use litepubl\view\Schemes;
use litepubl\view\Schema;
use litepubl\post\Posts;

function HomeInstall($self) {
     $self->getApp()->site->home = '/';
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
     $self->getApp()->router->unbind($self);
    Posts::unsub($self);

    $menus = Menus::i();
    $menus->lock();
    unset($menus->items[$menus->idhome]);
    $menus->sort();
    $menus->unlock();
}