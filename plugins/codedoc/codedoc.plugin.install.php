<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\LangMerger;
use litepubl\core\Plugins;
use litepubl\core\DBManager;

function tcodedocpluginInstall($self) {
    if (!dbversion) die("Ticket  system only for database version");
    $name = basename(dirname(__file__));
    $language =  $self->getApp()->options->language;
    $about = Plugins::getabout($name);
     $self->getApp()->classes->Add('tcodedocfilter', 'codedoc.filter.class.php', $name);
     $self->getApp()->classes->Add('tcodedocmenu', 'codedoc.menu.class.php', basename(dirname(__file__)));
    $menu = tcodedocmenu::i();
    $menu->url = '/doc/';
    $menu->title = $about['menutitle'];

    $menus = tmenus::i();
    $menus->add($menu);

    $merger = LangMerger::i();
    $merger->lock();
    $merger->add('codedoc', "plugins/$name/resource/$language.ini");
    $merger->add('codedoc', "plugins/$name/resource/html.ini");
    $merger->unlock();

    $manager = DBManager::i();
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

    $plugins = Plugins::i();
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

     $self->getApp()->classes->delete('tcodedocmenu');
     $self->getApp()->classes->delete('tcodedocfilter');

    $filter = tcontentfilter::i();
    $filter->unbind($self);

    $merger = LangMerger::i();
    $merger->delete('codedoc');

    $manager = DBManager::i();
    $manager->deletetable('codedoc');
}