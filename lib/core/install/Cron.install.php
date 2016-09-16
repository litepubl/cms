<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\core;

function CronInstall($self)
{
    $manager = $self->db->man;
    $manager->CreateTable('cron', file_get_contents(dirname(__file__) . '/sql/cron.sql'));

    $self->getApp()->router->add('/croncron.htm', get_class($self), null, 'get');

    $self->password = Str::md5Uniq();
    $self->addnightly('litepubl\core\Router', 'updatefilter', null);
    $self->addnightly('litepubl\tdboptimizer', 'optimize', null);
    $self->save();
}

function CronUninstall($self)
{
    Router::unsub($self);
}
