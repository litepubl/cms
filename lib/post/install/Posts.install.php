<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\post;

use litepubl\core\Cron;
use litepubl\core\Str;
use litepubl\widget\Widgets;

function PostsInstall($self)
{
    if (__NAMESPACE__ . '\Posts' != get_class($self)) {
        return;
    }

    $manager = $self->db->man;
    $dir = dirname(__file__) . '/sql/';
    $manager->CreateTable($self->table, file_get_contents($dir . 'posts.sql'));
    $manager->CreateTable('pages', file_get_contents($dir . 'pages.sql'));
    $manager->CreateTable($self->rawtable, file_get_contents($dir . 'raw.sql'));

    $Cron = Cron::i();
    $Cron->add('hour', get_class($self), 'HourCron');
}

function PostsUninstall($self)
{
    if ('litepubl\post\Posts' != get_class($self)) {
        return;
    }

    $Cron = Cron::i();
    $Cron->deleteclass(get_class($self));

    $widgets = Widgets::i();
    $widgets->deleteclass($self);
}

function PostsGetsitemap($self, $from, $count)
{
    $result = [];
    $commentpages = $self->getApp()->options->commentpages;
    $commentsperpage = $self->getApp()->options->commentsperpage;

    $db = $self->db;
    $now = Str::sqlDate();
    $res = $db->query(
        "select $db->posts.title, $db->posts.pagescount, $db->posts.commentscount, $db->urlmap.url
  from $db->posts, $db->urlmap
  where $db->posts.status = 'published' and $db->posts.posted < '$now' and $db->urlmap.id = $db->posts.idurl
  order by $db->posts.posted desc limit $from, $count"
    );
    while ($item = $db->fetchassoc($res)) {
        $comments = $commentpages ? ceil($item['commentscount'] / $commentsperpage) : 1;
        $result[] = [
            'url' => $item['url'],
            'title' => $item['title'],
            'pages' => max($item['pagescount'], $comments)
        ];
    }
    return $result;
}
