<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tuserpagesInstall($self) {
    if ($self->dbversion) {
        $manager = tdbmanager::i();
        $dir = dirname(__file__) . '/sql/';
        $manager->CreateTable($self->table, file_get_contents($dir . 'user.page.sql'));
    }

    $v = $self->createpage;
    $self->lock();
    $self->createpage = false;
    $self->add(1, 'Admin', litepubl::$options->email, litepubl::$site->url . '/');
    $itemurl = litepubl::$urlmap->findurl('/');
    $self->setvalue(1, 'idurl', $itemurl['id']);
    $self->createpage = $v;
    $self->unlock();

    $linkgen = tlinkgenerator::i();
    $linkgen->data['user'] = '/user/[name].htm';
    $linkgen->save();

    litepubl::$urlmap->add('/users.htm', get_class($self) , 'url', 'get');

    $robots = trobotstxt::i();
    $robots->AddDisallow('/users.htm');
}

function tuserpagesUninstall($self) {
    turlmap::unsub($self);
    turlmap::unsub($self);
}