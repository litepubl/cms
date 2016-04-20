<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\plugins\bootstrap;
use litepubl\core\Plugins;
use litepubl\admin\Menus;
use litepubl\view\LangMerger;

function AdminInstall($self) {
    Langmerger::i()->add('admin', 'plugins/bootstrap/resource/' .  $self->getApp()->options->language . '.admin.ini');
    $about = Plugins::getabout(Plugins::getname(__file__));

    $admin = Menus::i();
    $admin->lock();
    $admin->additem(array(
        'parent' => $admin->url2id('/admin/views/') ,
        'url' => '/admin/views/bootstraptheme/',
        'title' => $about['name'],
        'name' => 'bootstraptheme',
        'class' => get_class($self) ,
        'group' => 'admin'
    ));

Header::i()->install();
    $admin->unlock();
}

function AdminUninstall($self) {
    Langmerger::i()->deletefile('admin', 'plugins/bootstrap-theme/resource/' .  $self->getApp()->options->language . '.admin.ini');
    $admin = Menus::i();
    $admin->lock();
    $admin->deleteurl('/admin/views/bootstraptheme/');
Header::i()->uninstall();
    $admin->unlock();
}