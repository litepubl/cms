<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

use litepubl\core\DBManager;
use litepubl\core\Plugins;
use litepubl\view\Base;
use litepubl\view\Lang;
use litepubl\view\LangMerger;
use litepubl\view\MainView;

function tfoafInstall($self)
{
    $merger = LangMerger::i();
    $merger->addplugin(Plugins::getname(__file__));

    $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    $lang = Lang::i('foaf');

    if ($self->dbversion) {
        $manager = DBManager::i();
        $manager->createtable($self->table, file_get_contents($dir . 'foaf.sql'));
    }

    $actions = TXMLRPCAction::i();
    $actions->lock();
    $actions->add('invatefriend', get_class($self) , 'Invate');
    $actions->add('rejectfriend', get_class($self) , 'Reject');
    $actions->add('acceptfriend', get_class($self) , 'Accept');
    $actions->unlock();

    $router = $self->getApp()->router;
    $self->getApp()->router->unbind($self);
    $router->add('/foaf.xml', get_class($self) , null);

    $name = Plugins::getname(__file__);
    $classes = $self->getApp()->classes;
    $classes->lock();
    $classes->add('tadminfoaf', 'admin.foaf.class.php', $name);
    $classes->add('tfoafutil', 'foaf.util.class.php', $name);
    $classes->add('tprofile', 'profile.class.php', $name);
    $classes->add('tfriendswidget', 'widget.friends.class.php', $name);
    $classes->unlock();

    $admin = Menus::i();
    $admin->lock();
    $id = $admin->createitem(0, 'foaf', 'admin', 'tadminfoaf');
    {
        $admin->createitem($id, 'profile', 'admin', 'tadminfoaf');
        $admin->createitem($id, 'profiletemplate', 'admin', 'tadminfoaf');
    }
    $admin->unlock();

    $template = MainView::i();
    $template->addtohead('	<link rel="meta" type="application/rdf+xml" title="FOAF" href="$site.url/foaf.xml" />');
    $about = Plugins::getabout($name);
    $meta = tmetawidget::i();
    $meta->lock();
    $meta->add('foaf', '/foaf.xml', $about['name']);
    $meta->add('profile', '/profile.htm', $lang->profile);
    $meta->unlock();
    Base::clearCache();
}

function tfoafUninstall($self)
{
    $merger = LangMerger::i();
    $merger->deleteplugin(Plugins::getname(__file__));

    $actions = TXMLRPCAction::i();
    $actions->deleteclass(get_class($self));

    $router = $self->getApp()->router;
    $self->getApp()->router->unbind($self);

    $classes = $self->getApp()->classes;
    $classes->lock();
    $classes->delete('tfoafutil');
    $classes->delete('tprofile');
    $classes->delete('tfriendswidget');
    $classes->delete('tadminfoaf');
    $classes->unlock();

    $admin = Menus::i();
    $admin->lock();
    $admin->deleteurl('/admin/foaf/profiletemplate/');
    $admin->deleteurl('/admin/foaf/profile/');
    $admin->deleteurl('/admin/foaf/');
    $admin->unlock();

    if ($self->dbversion) {
        $manager = DBManager::i();
        $manager->deletetable($self->table);
    }

    $template = MainView::i();
    $template->deletefromhead('	<link rel="meta" type="application/rdf+xml" title="FOAF" href="$site.url/foaf.xml" />');

    $meta = tmetawidget::i();
    $meta->lock();
    $meta->delete('foaf');
    $meta->delete('profile');
    $meta->unlock();

    Base::clearCache();
}

