<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Lang;
use litepubl\view\Base;
use litepubl\view\Js;
use litepubl\view\LangMerger;
use litepubl\core\Plugins;
use litepubl\view\Theme;
use litepubl\view\Parser;
use litepubl\core\DBManager;
use litepubl\utils\LinkGenerator;

function tdownloaditemsInstall($self) {
    if (!dbversion) die("Downloads require database");
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    $manager = DBManager::i();
    $manager->CreateTable($self->childTable, file_get_contents($dir . 'downloaditem.sql'));
    $manager->addenum('posts', 'class', 'litepubl-tdownloaditem');

    $optimizer = tdboptimizer::i();
    $optimizer->lock();
    $optimizer->childTables[] = 'downloaditems';
    $optimizer->addevent('postsdeleted', get_class($self) , 'postsdeleted');
    $optimizer->unlock();

    LangMerger::i()->add('default', "plugins/" . basename(dirname(__file__)) . "/resource/" .  $self->getApp()->options->language . ".ini");

    $ini = parse_ini_file($dir .  $self->getApp()->options->language . '.install.ini', false);

    $tags = ttags::i();
     $self->getApp()->options->downloaditem_themetag = $tags->add(0, $ini['themetag']);
     $self->getApp()->options->downloaditem_plugintag = $tags->add(0, $ini['plugintag']);
    $base = basename(dirname(__file__));
    $classes =  $self->getApp()->classes;
    $classes->lock();
    /*
    //install polls if its needed
    $plugins = Plugins::i();
    if (!isset($plugins->items['polls'])) $plugins->add('polls');
    $polls = tpolls::i();
    $polls->garbage = false;
    $polls->save();
    */
    $classes->Add('tdownloaditem', 'downloaditem.class.php', $base);
    $classes->Add('tdownloaditemsmenu', 'downloaditems.menu.class.php', $base);
    $classes->Add('tdownloaditemeditor', 'admin.downloaditem.editor.class.php', $base);
    $classes->Add('tadmindownloaditems', 'admin.downloaditems.class.php', $base);
    $classes->Add('tdownloaditemcounter', 'downloaditem.counter.class.php', $base);
    $classes->Add('taboutparser', 'about.parser.class.php', $base);
    $classes->unlock();

    tadminhtml::i()->inidir(dirname(__file__) . '/resource/');
    $lang = Lang::i('downloaditems');
    $lang->ini['downloaditems'] = $lang->ini['downloaditem'] + $lang->ini['downloaditems'];

    $adminmenus = Menus::i();
    $adminmenus->lock();
    $parent = $adminmenus->createitem(0, 'downloaditems', 'editor', 'tadmindownloaditems');
    $adminmenus->items[$parent]['title'] = $lang->downloaditems;

    $idmenu = $adminmenus->createitem($parent, 'addurl', 'editor', 'tadmindownloaditems');
    $adminmenus->items[$idmenu]['title'] = $lang->addurl;

    $idmenu = $adminmenus->createitem($parent, 'editor', 'editor', 'tdownloaditemeditor');
    $adminmenus->items[$idmenu]['title'] = $lang->add;

    $idmenu = $adminmenus->createitem($parent, 'theme', 'editor', 'tadmindownloaditems');
    $adminmenus->items[$idmenu]['title'] = $lang->themes;

    $idmenu = $adminmenus->createitem($parent, 'plugin', 'editor', 'tadmindownloaditems');
    $adminmenus->items[$idmenu]['title'] = $lang->plugins;

    $adminmenus->unlock();

    $menus = tmenus::i();
    $menus->lock();
    $menu = tdownloaditemsmenu::i();
    $menu->type = '';
    $menu->url = '/downloads.htm';
    $menu->title = $ini['downloads'];
    $menu->content = '';
    $id = $menus->add($menu);
     $self->getApp()->router->db->setvalue($menu->idurl, 'type', 'get');

    foreach (array(
        'theme',
        'plugin'
    ) as $type) {
        $menu = tdownloaditemsmenu::i();
        $menu->type = $type;
        $menu->parent = $id;
        $menu->url = sprintf('/downloads/%ss.htm', $type);
        $menu->title = $lang->__get($type . 's');
        $menu->content = '';
        $menus->add($menu);
         $self->getApp()->router->db->setvalue($menu->idurl, 'type', 'get');
    }
    $menus->unlock();

    Js::i()->add('default', '/plugins/downloaditem/downloaditem.min.js');

    $parser = Parser::i();
    $parser->parsed = $self->themeparsed;
    Base::clearCache();

    $linkgen = LinkGenerator::i();
    $linkgen->data['downloaditem'] = '/[type]/[title].htm';
    $linkgen->save();
     $self->getApp()->options->savemodified();
}

function tdownloaditemsUninstall($self) {
    //die("Warning! You can lost all downloaditems!");
    tposts::unsub($self);

    $adminmenus = Menus::i();
    $adminmenus->deletetree($adminmenus->url2id('/admin/downloaditems/'));

    $menus = tmenus::i();
    $menus->deletetree($menus->class2id('tdownloaditemsmenu'));

    $parser = Parser::i();
    $parser->unbind($self);
    Base::clearCache();

    $classes =  $self->getApp()->classes;
    $classes->lock();
    $classes->delete('tdownloaditem');
    $classes->delete('tdownloaditemsmenu');
    $classes->delete('tdownloaditemeditor');
    $classes->delete('tadmindownloaditems');
    $classes->delete('tdownloaditemcounter');
    $classes->delete('taboutparser');
    $classes->unlock();

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

    Js::i()->deletefile('default', '/plugins/downloaditem/downloaditem.min.js');

     $self->getApp()->options->delete('downloaditem_themetag');
     $self->getApp()->options->delete('downloaditem_plugintag');
     $self->getApp()->options->savemodified();
}

function getd_download_js() {
    $result = '<script type="text/javascript">';
    $result.= "\n\$(document).ready(function() {\n";
    $result.= "if (\$(\"a[rel='theme'], a[rel='plugin']\").length) {\n";
    $result.= '$.load_script("$site.files/plugins/' . basename(dirname(__file__)) . "/downloaditem.min.js\");\n";
    $result.= "}\n";
    $result.= "});\n";
    $result.= "</script>";
    return $result;
}

function add_downloaditems_to_theme($theme) {
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