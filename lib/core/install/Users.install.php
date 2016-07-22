<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\core;

function UsersInstall($self)
{
    $manager = DBManager::i();
    $dir = dirname(__file__) . '/sql/';
    $manager->CreateTable($self->table, file_get_contents($dir . 'users.sql'));
    //$manager->setautoincrement($self->table, 2);
    $manager->CreateTable($self->grouptable, file_get_contents($dir . 'users.groups.sql'));

    $id = $self->db->add(
        array(
        'email' => $self->getApp()->options->email,
        'name' => $self->getApp()->site->author,
        'website' => $self->getApp()->site->url . '/',
        'password' => '',
        'cookie' => '',
        'expired' => Str::sqlDate() ,
        'status' => 'approved',
        'idgroups' => '1',
        )
    );

    $self->setgroups(
        $id, array(
        1
        )
    );
}

function UsersUninstall($self)
{
    //delete table
    
}
