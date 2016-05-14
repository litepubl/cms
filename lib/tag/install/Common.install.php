<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\tag;

use litepubl\post\Posts;
use litepubl\view\Schema;
use litepubl\widget\Widgets;

function CommonInstall($self)
{
    if ((__NAMESPACE__ . '\Common') == get_class($self)) {
        return;
    }

    $posts = Posts::i();
    $posts->lock();
    $posts->added = $self->postedited;
    $posts->edited = $self->postedited;
    $posts->deleted = $self->postdeleted;
    $posts->unlock();

    $self->getApp()->router->add("/$self->PermalinkIndex/", get_class($self) , 0);

    $manager = $self->db->man;
    $dir = dirname(__file__) . '/sql/';
    $manager->createtable($self->table, file_get_contents($dir . 'tags.sql'));
    $manager->createtable($self->itemsposts->table, file_get_contents(dirname(dirname(__DIR__)) . '/core/install/sql/ItemsPosts.sql'));
    $manager->createtable($self->contents->table, file_get_contents($dir . 'content.sql'));
}

function CommonUninstall($self)
{
    Posts::unsub($self);
    $self->getApp()->router->unbind($self);
    Widgets::i()->deleteclass(get_class($self));
}

function CommonGetsitemap($self, $from, $count)
{
    $result = array();
    $self->loadAll();
    $options = $self->getApp()->options;
    foreach ($self->items as $id => $item) {
        $schema = Schema::i($item['idschema']);
        $perpage = $schema->perpage ? $schema->perpage : $options->perpage;
        $pages = (int)ceil($item['itemscount'] / $perpage);

        $result[] = array(
            'url' => $item['url'],
            'title' => $item['title'],
            'pages' => $pages,
        );
    }

    return $result;
}

