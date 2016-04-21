<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\core\DBManager;

function texternallinksInstall($self) {
    if (dbversion) {
        $manager = DBManager::i();
        $manager->createtable($self->table, 'id int UNSIGNED NOT NULL auto_increment,
    clicked int UNSIGNED NOT NULL default 0,
    url varchar(255)not null,
    PRIMARY KEY(id),
    key url (url)
    ');
    } else {
    }

    $filter = tcontentfilter::i();
    $filter->lock();
    $filter->afterfilter = $self->filter;
    $filter->onaftercomment = $self->filter;
    $filter->unlock();

    $cron = tcron::i();
    $cron->add('hour', get_class($self) , 'updatestat');

     $self->getApp()->router->addget('/externallink.htm', get_class($self));

    $robot = trobotstxt::i();
    $robot->AddDisallow('/externallink.htm');
    tposts::i()->addrevision();
}

function texternallinksUninstall($self) {
    $filter = tcontentfilter::i();
    $filter->unbind($self);

    $cron = tcron::i();
    $cron->deleteclass(get_class($self));

     $self->getApp()->router->unbind($self);

    if (dbversion) {
        $manager = DBManager::i();
        $manager->deletetable($self->table);
    }
    tposts::i()->addrevision();
}