<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\core\DBManager;
use litepubl\view\Filter;

function twikiwordsInstall($self) {
    if ($self->dbversion) {
        $manager = DBManager::i();
        $manager->createtable($self->table, "  `id` int(10) unsigned NOT NULL auto_increment,
    `word` text NOT NULL,
    PRIMARY KEY  (`id`)");

        $manager->createtable($self->itemsposts->table, file_get_contents( $self->getApp()->paths->lib . 'install' . DIRECTORY_SEPARATOR . 'items.posts.sql'));
    }

    $filter = Filter::i();
    $filter->beforecontent = $self->beforefilter;

    $posts = tposts::i();
    $posts->deleted = $self->postdeleted;

     $self->getApp()->classes->classes['wikiwords'] = get_class($self);
     $self->getApp()->classes->save();
}

function twikiwordsUninstall($self) {
    unset( $self->getApp()->classes->classes['wikiword']);
     $self->getApp()->classes->save();

    $filter = Filter::i();
    $filter->unbind($self);

    tposts::unsub($self);
    if ($self->dbversion) {
        $manager = DBManager::i();
        $manager->deletetable($self->table);
        $manager->deletetable($self->itemsposts->table);
    }
}