<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\downloaditem;

use litepubl\core\DBManager;
use litepubl\core\Plugins;
use litepubl\utils\LinkGenerator;
use litepubl\view\Base;
use litepubl\view\Js;
use litepubl\view\Lang;
use litepubl\view\LangMerger;
use litepubl\view\Parser;
use litepubl\view\Theme;
use litepubl\tag\Tags;
use litepubl\pages\Menus;
use litepubl\admin\Menus as AdminMenus;

function PluginInstall($self)
{
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    $manager = DBManager::i();
    $manager->CreateTable($self->childTable, file_get_contents($dir . 'downloaditem.sql'));
    $manager->addEnum('posts', 'class', str_replace('\\', '-', __NAMESPACE__ . '\Item'));

    $optimizer = tdboptimizer::i();
    $optimizer->lock();
    $optimizer->childTables[] = 'downloaditems';
    $optimizer->addevent('postsdeleted', get_class($self) , 'postsdeleted');
    $optimizer->unlock();

    LangMerger::i()->addPlugin(basename(__DIR__));

Lang::usefile('install');
$lang = Lang::i('installdownloaditems');

    $tags = Tags::i();
$app = $self->getApp();
    $app->options->downloaditem_themetag = $tags->add(0, $lang->themetag);
    $app->options->downloaditem_plugintag = $tags->add(0, $lang->plugintag);
    $base = basename(dirname(__file__));
    $plugins = Plugins::i();
    if (!isset($plugins->items['polls'])) $plugins->add('polls');
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
    $menu = new Menui();
    $menu->type = '';
    $menu->url = '/downloads.htm';
    $menu->title = $lang->downloads;
    $menu->content = '';
    $id = $menus->add($menu);
$app->router->db->setvalue($menu->idurl, 'type', 'get');

    foreach (array(
        'theme',
        'plugin'
    ) as $type) {
        $menu = new Menu();
        $menu->type = $type;
        $menu->parent = $id;
        $menu->url = sprintf('/downloads/%ss.htm', $type);
        $menu->title = $lang->__get($type . 's');
        $menu->content = '';
        $menus->add($menu);
$app->router->db->setvalue($menu->idurl, 'type', 'get');
    }
    $menus->unlock();

    Js::i()->add('default', '/plugins/downloaditem/resource/downloaditem.min.js');

    $parser = Parser::i();
    $parser->parsed = $self->themeparsed;
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
    Posts::unsub($self);

    $adminmenus = AdminMenus::i();
    $adminmenus->deleteTree($adminmenus->url2id('/admin/downloaditems/'));

    $menus = Menus::i();
    $menus->deleteTree($menus->class2id(__NAMESPACE__ . '\Menu'));

    $parser = Parser::i();
    $parser->unbind($self);
    $parser->removeTags('plugins/downloaditem/resource/theme.txt', 'plugins/downloaditem/resource/theme.ini');
    Base::clearCache();

Counter::i()->uninstall();

    $merger = LangMerger::i();
    $merger->deleteplugin(Plugins::getname(__file__));

    $manager = DBManager::i();
    $manager->deletetable($self->childTable);
    $manager->delete_enum('posts', 'class', 'tdownloaditem');

    $optimizer = tdboptimizer::i();
    $optimizer->lock();
    $optimizer->unbind($self);
    if (false !== ($i = array_search('downloaditems', $optimizer->childTables))) {
        unset($optimizer->childTables[$i]);
    }
    $optimizer->unlock();

    Js::i()->deletefile('default', '/plugins/downloaditem/resource/downloaditem.min.js');

$app = $self->getApp();
    $app->options->delete('downloaditem_themetag');
    $app->options->delete('downloaditem_plugintag');
    $app->poolStorage->commit();
}

function getd_download_js()
{
    $result = '<script type="text/javascript">';
    $result.= "\n\$(document).ready(function() {\n";
    $result.= "if (\$(\"a[rel='theme'], a[rel='plugin']\").length) {\n";
    $result.= '$.load_script("$site.files/plugins/' . basename(dirname(__file__)) . "/downloaditem.min.js\");\n";
    $result.= "}\n";
    $result.= "});\n";
    $result.= "</script>";
    return $result;
}

function add_downloaditems_to_theme($theme)
{
    if (empty($theme->templates['custom']['downloadexcerpt'])) {
        $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
        Theme::$vars['lang'] = Lang::admin('downloaditems');
        $custom = & $theme->templates['custom'];
        $custom['downloaditem'] = $theme->replacelang(file_get_contents($dir . 'downloaditem.tml') , Lang::i('downloaditem'));
        $lang = Lang::i('downloaditems');
        $custom['downloadexcerpt'] = $theme->replacelang(file_get_contents($dir . 'downloadexcerpt.tml') , $lang);
        $custom['siteform'] = $theme->parse(file_get_contents($dir . 'siteform.tml'));

        //admin
        $admin = & $theme->templates['customadmin'];
        $admin['downloadexcerpt'] = array(
            'type' => 'editor',
            'title' => $lang->downloadexcerpt
        );

        $admin['downloaditem'] = array(
            'type' => 'editor',
            'title' => $lang->downloadlinks
        );

        $admin['siteform'] = array(
            'type' => 'editor',
            'title' => $lang->siteform
        );
    }
    //var_dump($theme->templates['customadmin'], $theme->templates['custom']);
    
}

