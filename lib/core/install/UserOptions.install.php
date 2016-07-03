<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\core;

function UserOptionsInstall($self)
{
    $self->defvalues['subscribe'] = 'enabled';
    $options = $self->getApp()->options;
    if (isset($options->defaultsubscribe)) {
        $self->defvalues['subscribe'] = $options->defaultsubscribe ? 'enabled' : 'disabled';
    }

    $self->defvalues['authorpost_subscribe'] = 'enabled';
    $self->save();

    $manager = DBManager::i();
    $manager->CreateTable($self->table, file_get_contents(dirname(__file__) . '/sql/user.options.sql'));
}

function UserOptionsUninstall($self)
{
    DBManager::i()->deletetable($self->table);
}
