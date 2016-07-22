<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\plugins\sameposts;

use litepubl\core\DBManager;
use litepubl\post\Posts;

function WidgetInstall($self)
{
    $manager = DBManager::i();
    $manager->createTable(
        $self->table, 'id int UNSIGNED NOT NULL default 0,
    items text NOT NULL,
    PRIMARY KEY(id) '
    );

    $widgets = $self->getWidgets();
    $widgets->addClass($self, $self::POSTCLASS);

    $posts = Posts::i();
    $posts->changed = $self->postsChanged;
}

function WidgetUninstall($self)
{
    Posts::unsub($self);
    $self->getWidgets()->deleteClass(get_class($self));

    $manager = DBManager::i();
    $manager->deleteTable($self->table);
}
