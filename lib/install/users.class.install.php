<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function tusersInstall($self) {
  $manager = tdbmanager::i();
  $dir = dirname(__file__) . '/sql/';
  $manager->CreateTable($self->table, file_get_contents($dir . 'user.sql'));
  //$manager->setautoincrement($self->table, 2);
  $manager->CreateTable($self->grouptable, file_get_contents($dir . 'user.groups.sql'));

  $id = $self->db->add(array(
    'email' => litepublisher::$options->email,
    'name' => litepublisher::$site->author,
    'website' => litepublisher::$site->url . '/',
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

function tusersUninstall($self) {
  //delete table
  
}