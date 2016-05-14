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

use litepubl\Config;
use litepubl\core\DBManager;
use litepubl\core\Plugins;
use litepubl\utils\LinkGenerator;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\LangMerger;

function tticketsInstall($self)
{
    if (version_compare(PHP_VERSION, '5.3', '<')) {
        die('Ticket system requires PHP 5.3 or later. You are using PHP ' . PHP_VERSION);
    }

    $dirname = basename(dirname(__file__));
    LangMerger::i()->addplugin($dirname);
    $lang = Lang::admin('tickets');
    $lang->addsearch('ticket', 'tickets');

    $self->data['cats'] = array();
    $self->data['idcomauthor'] = tusers::i()->add(array(
        'email' => '',
        'name' => Lang::get('ticket', 'comname') ,
        'status' => 'approved',
        'idgroups' => 'commentator'
    ));

    $self->save();

    $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    $filter = Filter::i();
    $filter->phpcode = true;
    $filter->save();
    $self->getApp()->options->parsepost = false;

    $manager = DBManager::i();
    $manager->CreateTable($self->childTable, file_get_contents($dir . 'ticket.sql'));
    $manager->addenum('posts', 'class', 'litepubl-tticket');

    $optimizer = tdboptimizer::i();
    $optimizer->lock();
    $optimizer->childTables[] = 'tickets';
    $optimizer->addevent('postsdeleted', 'ttickets', 'postsdeleted');
    $optimizer->unlock();

    $self->getApp()->classes->lock();
    //install polls if its needed
    $plugins = Plugins::i();
    if (!isset($plugins->items['polls'])) $plugins->add('polls');

    $self->getApp()->classes->Add('tticket', 'ticket.class.php', $dirname);
    // $self->getApp()->classes->Add('tticketsmenu', 'tickets.menu.class.php', $dirname);
    $self->getApp()->classes->Add('tticketeditor', 'admin.ticketeditor.class.php', $dirname);
    $self->getApp()->classes->Add('tadmintickets', 'admin.tickets.class.php', $dirname);
    $self->getApp()->classes->Add('tadminticketoptions', 'admin.tickets.options.php', $dirname);

    $self->getApp()->options->reguser = true;
    $adminsecure = adminsecure::i();
    $adminsecure->usersenabled = true;

    $adminmenus = Menus::i();
    $adminmenus->lock();

    $parent = $adminmenus->createitem(0, 'tickets', 'ticket', 'tadmintickets');
    $adminmenus->items[$parent]['title'] = Lang::get('tickets', 'tickets');

    $idmenu = $adminmenus->createitem($parent, 'editor', 'ticket', 'tticketeditor');
    $adminmenus->items[$idmenu]['title'] = Lang::get('tickets', 'editortitle');

    $idmenu = $adminmenus->createitem($parent, 'opened', 'ticket', 'tadmintickets');
    $adminmenus->items[$idmenu]['title'] = Lang::get('ticket', 'opened');

    $idmenu = $adminmenus->createitem($parent, 'fixed', 'ticket', 'tadmintickets');
    $adminmenus->items[$idmenu]['title'] = Lang::get('ticket', 'fixed');

    $idmenu = $adminmenus->createitem($parent, 'options', 'admin', 'tadminticketoptions');
    $adminmenus->items[$idmenu]['title'] = Lang::i()->options;

    $adminmenus->onexclude = $self->onexclude;
    $adminmenus->unlock();
    /*
    $menus = tmenus::i();
    $menus->lock();
    $ini = parse_ini_file($dir .  $self->getApp()->options->language . '.install.ini', false);
    
    $menu = tticketsmenu::i();
    $menu->type = 'tickets';
    $menu->url = '/tickets/';
    $menu->title = $ini['tickets'];
    $menu->content = $ini['contenttickets'];
    $id = $menus->add($menu);
    
    foreach (array('bug', 'feature', 'support', 'task') as $type) {
    $menu = tticketsmenu::i();
    $menu->type = $type;
    $menu->parent = $id;
    $menu->url = "/$type/";
    $menu->title = $ini[$type];
    $menu->content = '';
    $menus->add($menu);
    }
    $menus->unlock();
    */
    $self->getApp()->classes->unlock();

    $linkgen = LinkGenerator::i();
    $linkgen->data['ticket'] = '/tickets/[title].htm';
    $linkgen->save();

    $groups = tusergroups::i();
    $groups->lock();
    $idticket = $groups->add('ticket', 'Tickets', '/admin/tickets/editor/');
    $groups->defaults = array(
        $idticket,
        $groups->getidgroup('author')
    );
    $groups->items[$self->getApp()->options->groupnames['author']]['parents'][] = $idticket;
    $groups->items[$self->getApp()->options->groupnames['commentator']]['parents'][] = $idticket;
    $groups->unlock();
}

function tticketsUninstall($self)
{
    //die("Warning! You can lost all tickets!");
    $self->getApp()->classes->lock();
    //if (Config::$debug)  $self->getApp()->classes->delete('tpostclasses');
    tposts::unsub($self);

    $self->getApp()->classes->delete('tticket');
    $self->getApp()->classes->delete('tticketeditor');
    $self->getApp()->classes->delete('tadmintickets');
    $self->getApp()->classes->delete('tadminticketoptions');

    $adminmenus = Menus::i();
    $adminmenus->lock();
    $adminmenus->deletetree($adminmenus->url2id('/admin/tickets/'));
    $adminmenus->unbind($self);
    $adminmenus->unlock();
    /*
    $menus = tmenus::i();
    $menus->lock();
    foreach (array('bug', 'feature', 'support', 'task') as $type) {
    $menus->deleteurl("/$type/");
    }
    $menus->deleteurl('/tickets/');
    $menus->unlock();
    
     $self->getApp()->classes->delete('tticketsmenu');
    */
    $self->getApp()->classes->unlock();

    $manager = DBManager::i();
    $manager->deletetable($self->childTable);
    $manager->delete_enum('posts', 'class', 'tticket');

    $optimizer = tdboptimizer::i();
    $optimizer->lock();
    $optimizer->unbind($self);
    if (false !== ($i = array_search('tickets', $optimizer->childTables))) {
        unset($optimizer->childTables[$i]);
    }
    $optimizer->unlock();

    LangMerger::i()->deleteplugin(Plugins::getname(__file__));
}

