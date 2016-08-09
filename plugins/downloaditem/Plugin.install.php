<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\plugins\downloaditem;

use litepubl\admin\Menus as AdminMenus;
use litepubl\core\DBManager;
use litepubl\core\DBOptimizer;
use litepubl\core\Plugins;
use litepubl\pages\FakeMenu;
use litepubl\pages\Menus;
use litepubl\post\Posts;
use litepubl\tag\Tags;
use litepubl\utils\LinkGenerator;
use litepubl\view\Base;
use litepubl\view\Lang;
use litepubl\view\LangMerger;
use litepubl\view\Parser;
use litepubl\view\Theme;

function PluginInstall($self)
{
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    $manager = DBManager::i();
    $manager->CreateTable($self->childTable, file_get_contents($dir . 'downloaditem.sql'));
    $manager->addEnum('posts', 'class', str_replace('\\', '-', __NAMESPACE__ . '\Item'));

    $optimizer = DBOptimizer::i();
    $optimizer->lock();
    $optimizer->childTables[] = 'downloaditems';
    $optimizer->unlock();

    LangMerger::i()->addPlugin(basename(__DIR__));

    Lang::usefile('install');
    $lang = Lang::i('installdownloaditems');

    $tags = Tags::i();
    $idparent = $tags->add(0, $lang->downloads);
    $tags->setValue($idparent, 'includechilds', '1');

    $idplugin = $tags->add($idparent, $lang->plugintag);
    $idtheme = $tags->add($idparent, $lang->themetag);
    $tags->setValue($idplugin, 'includechilds', '1');
    $tags->setValue($idtheme, 'includechilds', '1');

    $app = $self->getApp();
    $app->options->downloaditem_themetag = $idtheme;
    $app->options->downloaditem_plugintag = $idplugin;

    $plugins = Plugins::i();
    if (!isset($plugins->items['polls'])) {
        $plugins->add('polls');
    }
    Counter::i()->install();

    Lang::usefile('admin');
    $lang->addSearch('downloaditem', 'downloaditems');

    $adminmenus = AdminMenus::i();
    $adminmenus->lock();
    $parent = $adminmenus->createitem(0, 'downloaditems', 'editor', __NAMESPACE__ . '\Admin');
    $adminmenus->items[$parent]['title'] = $lang->downloaditems;

    $idmenu = $adminmenus->createitem($parent, 'addurl', 'editor', __NAMESPACE__ . '\Admin');
    $adminmenus->items[$idmenu]['title'] = $lang->addurl;

    $idmenu = $adminmenus->createitem($parent, 'editor', 'editor', __NAMESPACE__ . '\Editor');
    $adminmenus->items[$idmenu]['title'] = $lang->add;

    $idmenu = $adminmenus->createitem($parent, 'theme', 'editor', __NAMESPACE__ . '\Admin');
    $adminmenus->items[$idmenu]['title'] = $lang->themes;

    $idmenu = $adminmenus->createitem($parent, 'plugin', 'editor', __NAMESPACE__ . '\Admin');
    $adminmenus->items[$idmenu]['title'] = $lang->plugins;

    $adminmenus->unlock();

    $menus = Menus::i();
    $menus->lock();

    $tags->loadAll();
    $item = $tags->getItem($idparent);
    $menu = new FakeMenu();
    $menu->url = $item['url'];
    $menu->title = $item['title'];
    $id = $menus->addFakeMenu($menu);

    $item = $tags->getItem($idplugin);
        $menu = new FakeMenu();
        $menu->parent = $id;
        $menu->url = $item['url'];
    //sprintf('/downloads/%ss.htm', $type);
        $menu->title = $item['title'];
        $menus->addFakeMenu($menu);


    $item = $tags->getItem($idtheme);
        $menu = new FakeMenu();
        $menu->parent = $id;
        $menu->url = $item['url'];
    //sprintf('/downloads/%ss.htm', $type);
        $menu->title = $item['title'];
        $menus->addFakeMenu($menu);

    $menus->unlock();

    $parser = Parser::i();
    $parser->addTags('plugins/downloaditem/resource/theme.txt', 'plugins/downloaditem/resource/theme.ini');
    Base::clearCache();

    $linkgen = LinkGenerator::i();
    $linkgen->data['downloaditem'] = '/[type]/[title].htm';
    $linkgen->save();
    $app->poolStorage->commit();
}

function PluginUninstall($self)
{
    //die("Warning! You can lost all downloaditems!");
    $app = $self->getApp();
    Posts::unsub($self);

    $adminmenus = AdminMenus::i();
    $adminmenus->deleteTree($adminmenus->url2id('/admin/downloaditems/'));

    $tags = Tags::i();
    $tags->loadAll();
    $item = $tags->getItem($app->options->downloaditem_plugintag);
    $item = $tags->getItem($item['parent']);
    $menus = Menus::i();
    $menus->deleteTree($menus->url2id($item['url']));

    $parser = Parser::i();
    $parser->removeTags('plugins/downloaditem/resource/theme.txt', 'plugins/downloaditem/resource/theme.ini');
    Base::clearCache();

    Counter::i()->uninstall();

    $merger = LangMerger::i();
    $merger->deleteplugin(Plugins::getname(__file__));

    $manager = DBManager::i();
    $manager->deletetable($self->childTable);
    $manager->deleteEnum('posts', 'class', 'tdownloaditem');

    $optimizer = DBOptimizer::i();
    $optimizer->lock();
    $optimizer->unbind($self);
    if (false !== ($i = array_search('downloaditems', $optimizer->childTables))) {
        unset($optimizer->childTables[$i]);
    }
    $optimizer->unlock();

    $app->options->delete('downloaditem_themetag');
    $app->options->delete('downloaditem_plugintag');
    $app->poolStorage->commit();
}
