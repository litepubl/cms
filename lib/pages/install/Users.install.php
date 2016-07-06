<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\pages;

use litepubl\utils\LinkGenerator;

function UsersInstall($self)
{
    $manager = $self->db->man;
    $dir = dirname(__file__) . '/sql/';
    $manager->CreateTable($self->table, file_get_contents($dir . 'users.sql'));

    $v = $self->createpage;
    $self->lock();
    $self->createpage = false;
    $self->add(1, 'Admin', $self->getApp()->options->email, $self->getApp()->site->url . '/');
    $itemurl = $self->getApp()->router->findurl('/');
    $self->setvalue(1, 'idurl', $itemurl['id']);
    $self->createpage = $v;
    $self->unlock();

    $linkgen = LinkGenerator::i();
    $linkgen->data['user'] = '/user/[name].htm';
    $linkgen->save();

    $self->getApp()->router->add('/users.htm', get_class($self), 'url', 'get');

    $robots = RobotsTxt::i();
    $robots->AddDisallow('/users.htm');
}

function UsersUninstall($self)
{
    $self->getApp()->router->unbind($self);
}
