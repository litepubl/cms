<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tticketsInstall($self) {
  if (version_compare(PHP_VERSION, '5.3', '<')) {
    die('Ticket system requires PHP 5.3 or later. You are using PHP ' . PHP_VERSION);
  }

  $dirname = basename(dirname(__file__));
  tlocalmerger::i()->addplugin($dirname);
  $lang = tlocal::admin('tickets');
  $lang->addsearch('ticket', 'tickets');

  $self->data['cats'] = array();
  $self->data['idcomauthor'] = tusers::i()->add(array(
    'email' => '',
    'name' => tlocal::get('ticket', 'comname') ,
    'status' => 'approved',
    'idgroups' => 'commentator'
  ));

  $self->save();

  $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  $filter = tcontentfilter::i();
  $filter->phpcode = true;
  $filter->save();
  litepubl::$options->parsepost = false;

  $manager = tdbmanager::i();
  $manager->CreateTable($self->childtable, file_get_contents($dir . 'ticket.sql'));
  $manager->addenum('posts', 'class', 'litepubl-tticket');

  $optimizer = tdboptimizer::i();
  $optimizer->lock();
  $optimizer->childtables[] = 'tickets';
  $optimizer->addevent('postsdeleted', 'ttickets', 'postsdeleted');
  $optimizer->unlock();

  litepubl::$classes->lock();
  //install polls if its needed
  $plugins = tplugins::i();
  if (!isset($plugins->items['polls'])) $plugins->add('polls');

  litepubl::$classes->Add('tticket', 'ticket.class.php', $dirname);
  //litepubl::$classes->Add('tticketsmenu', 'tickets.menu.class.php', $dirname);
  litepubl::$classes->Add('tticketeditor', 'admin.ticketeditor.class.php', $dirname);
  litepubl::$classes->Add('tadmintickets', 'admin.tickets.class.php', $dirname);
  litepubl::$classes->Add('tadminticketoptions', 'admin.tickets.options.php', $dirname);

  litepubl::$options->reguser = true;
  $adminsecure = adminsecure::i();
  $adminsecure->usersenabled = true;

  $adminmenus = tadminmenus::i();
  $adminmenus->lock();

  $parent = $adminmenus->createitem(0, 'tickets', 'ticket', 'tadmintickets');
  $adminmenus->items[$parent]['title'] = tlocal::get('tickets', 'tickets');

  $idmenu = $adminmenus->createitem($parent, 'editor', 'ticket', 'tticketeditor');
  $adminmenus->items[$idmenu]['title'] = tlocal::get('tickets', 'editortitle');

  $idmenu = $adminmenus->createitem($parent, 'opened', 'ticket', 'tadmintickets');
  $adminmenus->items[$idmenu]['title'] = tlocal::get('ticket', 'opened');

  $idmenu = $adminmenus->createitem($parent, 'fixed', 'ticket', 'tadmintickets');
  $adminmenus->items[$idmenu]['title'] = tlocal::get('ticket', 'fixed');

  $idmenu = $adminmenus->createitem($parent, 'options', 'admin', 'tadminticketoptions');
  $adminmenus->items[$idmenu]['title'] = tlocal::i()->options;

  $adminmenus->onexclude = $self->onexclude;
  $adminmenus->unlock();
  /*
  $menus = tmenus::i();
  $menus->lock();
  $ini = parse_ini_file($dir . litepubl::$options->language . '.install.ini', false);
  
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
  litepubl::$classes->unlock();

  $linkgen = tlinkgenerator::i();
  $linkgen->data['ticket'] = '/tickets/[title].htm';
  $linkgen->save();

  $groups = tusergroups::i();
  $groups->lock();
  $idticket = $groups->add('ticket', 'Tickets', '/admin/tickets/editor/');
  $groups->defaults = array(
    $idticket,
    $groups->getidgroup('author')
  );
  $groups->items[litepubl::$options->groupnames['author']]['parents'][] = $idticket;
  $groups->items[litepubl::$options->groupnames['commentator']]['parents'][] = $idticket;
  $groups->unlock();
}

function tticketsUninstall($self) {
  //die("Warning! You can lost all tickets!");
  litepubl::$classes->lock();
  //if (litepubl::$debug) litepubl::$classes->delete('tpostclasses');
  tposts::unsub($self);

  litepubl::$classes->delete('tticket');
  litepubl::$classes->delete('tticketeditor');
  litepubl::$classes->delete('tadmintickets');
  litepubl::$classes->delete('tadminticketoptions');

  $adminmenus = tadminmenus::i();
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
  
  litepubl::$classes->delete('tticketsmenu');
  */
  litepubl::$classes->unlock();

  $manager = tdbmanager::i();
  $manager->deletetable($self->childtable);
  $manager->delete_enum('posts', 'class', 'tticket');

  $optimizer = tdboptimizer::i();
  $optimizer->lock();
  $optimizer->unbind($self);
  if (false !== ($i = array_search('tickets', $optimizer->childtables))) {
    unset($optimizer->childtables[$i]);
  }
  $optimizer->unlock();

  tlocalmerger::i()->deleteplugin(tplugins::getname(__file__));
}