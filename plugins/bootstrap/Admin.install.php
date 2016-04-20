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

function AdminInstall($self) {
    Langmerger::i()->add('admin', 'plugins/bootstrap/resource/' .  $self->getApp()->options->language . '.admin.ini');
    $about = Plugins::getabout(Plugins::getname(__file__));

    $admin = tadminmenus::i();
    $admin->lock();
    $admin->additem(array(
        'parent' => $admin->url2id('/admin/views/') ,
        'url' => '/admin/views/bootstraptheme/',
        'title' => $about['name'],
        'name' => 'bootstraptheme',
        'class' => get_class($self) ,
        'group' => 'admin'
    ));

     $self->getApp()->classes->add('admin_bootstrap_header', 'admin.bootstrap-header.php', basename(dirname(__file__)));
    $admin->unlock();
}

function AdninUninstall($self) {
    Langmerger::i()->deletefile('admin', 'plugins/bootstrap-theme/resource/' .  $self->getApp()->options->language . '.admin.ini');
    $admin = tadminmenus::i();
    $admin->lock();
    $admin->deleteurl('/admin/views/bootstraptheme/');
     $self->getApp()->classes->delete('admin_bootstrap_header');
    $admin->unlock();
}