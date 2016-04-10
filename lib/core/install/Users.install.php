<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;

function UsersInstall($self) {
    $manager = tdbmanager::i();
    $dir = dirname(__file__) . '/sql/';
    $manager->CreateTable($self->table, file_get_contents($dir . 'users.sql'));
    //$manager->setautoincrement($self->table, 2);
    $manager->CreateTable($self->grouptable, file_get_contents($dir . 'users.groups.sql'));

    $id = $self->db->add(array(
        'email' => litepubl::$options->email,
        'name' => litepubl::$site->author,
        'website' => litepubl::$site->url . '/',
        'password' => '',
        'cookie' => '',
        'expired' => sqldate() ,
        'status' => 'approved',
        'idgroups' => '1',
    ));

    $self->setgroups($id, array(
        1
    ));
}

function UsersUninstall($self) {
    //delete table
    }