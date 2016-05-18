<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\tickets;

use litepubl\Config;
use litepubl\core\DBManager;
use litepubl\core\Plugins;
use litepubl\utils\LinkGenerator;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\LangMerger;
use litepubl\core\Users;
use litepubl\core\UserGroups;
use litepubl\admin\Menus as AdminMenus;
use litepubl\post\Posts;

function TicketsInstall($self)
{
    $dirname = basename(dirname(__file__));
    LangMerger::i()->addPlugin($dirname);
    $lang = Lang::admin('tickets');
    $lang->addSearch('ticket', 'tickets');

    $self->data['cats'] = array();
    $self->data['idcomauthor'] = Users::i()->add(array(
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

$app = $self->getApp();
$app->options->parsepost = false;

    $manager = DBManager::i();
    $manager->CreateTable($self->childTable, file_get_contents($dir . 'ticket.sql'));
    $manager->addEnum('posts', 'class', str_replace('\\', '-', __NAMESPACE__ . '\Ticket'));

    $optimizer = tdboptimizer::i();
    $optimizer->lock();
    $optimizer->childTables[] = 'tickets';
    $optimizer->addevent('postsdeleted', 'ttickets', 'postsdeleted');
    $optimizer->unlock();

    //install polls if its needed
    $plugins = Plugins::i();
    if (!isset($plugins->items['polls'])) {
$plugins->add('polls');
}

    $app->options->reguser = true;
    $adminsecure = adminsecure::i();
    $adminsecure->usersenabled = true;

$ns = __NAMESPACE__ . '\\';
    $adminmenus = AdminMenus::i();
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

    $linkgen = LinkGenerator::i();
    $linkgen->data['ticket'] = '/tickets/[title].htm';
    $linkgen->save();

    $groups = UserGroups::i();
    $groups->lock();
    $idticket = $groups->add('ticket', 'Tickets', '/admin/tickets/editor/');
    $groups->defaults = array(
        $idticket,
        $groups->getidgroup('author')
    );
    $groups->items[$app->options->groupnames['author']]['parents'][] = $idticket;
    $groups->items[$app->options->groupnames['commentator']]['parents'][] = $idticket;
    $groups->unlock();
}

function TicketsUninstall($self)
{
    Posts::unsub($self);

    $app->classes->delete('tticket');
    $adminmenus = AdminMenus::i();
    $adminmenus->lock();
    $adminmenus->deletetree($adminmenus->url2id('/admin/tickets/'));
    $adminmenus->unbind($self);
    $adminmenus->unlock();

    $manager = DBManager::i();
    $manager->deleteTable($self->childTable);
    $manager->deleteEnum('posts', 'class', 'tticket');

    $optimizer = tdboptimizer::i();
    $optimizer->lock();
    $optimizer->unbind($self);
    if (false !== ($i = array_search('tickets', $optimizer->childTables))) {
        unset($optimizer->childTables[$i]);
    }
    $optimizer->unlock();

    LangMerger::i()->deletePlugin(Plugins::getname(__file__));
}

