<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\externallinks;

use litepubl\core\Cron;
use litepubl\core\DBManager;
use litepubl\pages\RobotsTxt;
use litepubl\post\Posts;
use litepubl\view\Filter;

function ExternalLinksInstall($self)
{
    $manager = DBManager::i();
    $manager->createtable($self->table, 'id int UNSIGNED NOT NULL auto_increment,
    clicked int UNSIGNED NOT NULL default 0,
    url varchar(255)not null,
    PRIMARY KEY(id),
    key url (url)
    ');

    $filter = Filter::i();
    $filter->lock();
    $filter->afterfilter = $self->filter;
    $filter->onaftercomment = $self->filter;
    $filter->unlock();

    $cron = Cron::i();
    $cron->add('hour', get_class($self) , 'updatestat');

    $self->getApp()->router->addget('/externallink.htm', get_class($self));

    $robot = RobotsTxt::i();
    $robot->AddDisallow('/externallink.htm');
    Posts::i()->addRevision();
}

function ExternalLinksUninstall($self)
{
    $filter = Filter::i();
    $filter->unbind($self);

    $cron = Cron::i();
    $cron->deleteClass(get_class($self));

    $self->getApp()->router->unbind($self);

    $manager = DBManager::i();
    $manager->deletetable($self->table);
    Posts::i()->addRevision();
}

