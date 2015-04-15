<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tusersInstall($self) {
  $manager = tdbmanager::i();
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
  $manager->CreateTable($self->table, file_get_contents($dir .'users.sql'));
  //$manager->setautoincrement($self->table, 2);
  $manager->CreateTable($self->grouptable, file_get_contents($dir .'usersgroups.sql'));
  
  $id = $self->db->add(array(
  'email' =>litepublisher::$options->email,
  'name' => litepublisher::$site->author,
  'website' => litepublisher::$site->url . '/',
  'password' => '',
  'cookie' => '',
  'expired' => sqldate(),
  'status' => 'approved',
  'idgroups' => '1',
  ));
  
  $self->setgroups($id, array(1));
}

function tusersUninstall($self) {
  //delete table
}