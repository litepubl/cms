<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function tcodedocpluginInstall($self) {
    if (!dbversion) die("Ticket  system only for database version");
    $name = basename(dirname(__file__));
    $language = litepubl::$options->language;
    $about = tplugins::getabout($name);
    litepubl::$classes->Add('tcodedocfilter', 'codedoc.filter.class.php', $name);
    litepubl::$classes->Add('tcodedocmenu', 'codedoc.menu.class.php', basename(dirname(__file__)));
    $menu = tcodedocmenu::i();
    $menu->url = '/doc/';
    $menu->title = $about['menutitle'];

    $menus = tmenus::i();
    $menus->add($menu);

    $merger = tlocalmerger::i();
    $merger->lock();
    $merger->add('codedoc', "plugins/$name/resource/$language.ini");
    $merger->add('codedoc', "plugins/$name/resource/html.ini");
    $merger->unlock();

    $manager = tdbmanager::i();
    $manager->CreateTable('codedoc', '
  id int unsigned NOT NULL default 0,
  class varchar(32) NOT NULL,
  parentclass varchar(32) NOT NULL,
  methods text not null,
  props text not null,
  events text not null,
  
  KEY id (id),
  KEY parentclass (parentclass)
  ');

    $filter = tcontentfilter::i();
    $filter->lock();
    $filter->beforecontent = $self->filterpost;
    $filter->seteventorder('beforecontent', $self, 0);

    $plugins = tplugins::i();
    if (!isset($plugins->items['wikiwords'])) $plugins->add('wikiwords');

    $filter->beforecontent = $self->afterfilter;
    $filter->unlock();

    $linkgen = tlinkgenerator::i();
    $linkgen->data['codedoc'] = '/doc/[title].htm';
    $linkgen->save();

    tposts::i()->deleted = $self->postdeleted;
}

function tcodedocpluginUninstall($self) {
    //die("Warning! You can lost all tickets!");
    tposts::unsub($self);

    $menus = tmenus::i();
    $menus->deleteurl('/doc/');

    litepubl::$classes->delete('tcodedocmenu');
    litepubl::$classes->delete('tcodedocfilter');

    $filter = tcontentfilter::i();
    $filter->unbind($self);

    $merger = tlocalmerger::i();
    $merger->delete('codedoc');

    $manager = tdbmanager::i();
    $manager->deletetable('codedoc');
}