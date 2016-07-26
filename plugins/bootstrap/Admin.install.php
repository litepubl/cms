<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\plugins\bootstrap;

use litepubl\admin\Menus;
use litepubl\core\Plugins;
use litepubl\view\LangMerger;

function AdminInstall($self)
{
    LangMerger::i()->add('admin', 'plugins/bootstrap/resource/' . $self->getApp()->options->language . '.admin.ini');
    $about = Plugins::getabout(Plugins::getname(__file__));

    $admin = Menus::i();
    $admin->lock();
    $admin->additem(
        array(
        'parent' => $admin->url2id('/admin/views/') ,
        'url' => '/admin/views/bootstraptheme/',
        'title' => $about['name'],
        'name' => 'bootstraptheme',
        'class' => get_class($self) ,
        'group' => 'admin'
        )
    );

    Header::i()->install();
    $admin->unlock();
}

function AdminUninstall($self)
{
    LangMerger::i()->deletefile('admin', 'plugins/bootstrap-theme/resource/' . $self->getApp()->options->language . '.admin.ini');
    $admin = Menus::i();
    $admin->lock();
    $admin->deleteurl('/admin/views/bootstraptheme/');
    Header::i()->uninstall();
    $admin->unlock();
}
