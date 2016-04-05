<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function tpostsInstall($self) {
    if ('litepubl\tposts' != get_class($self)) return;

    $manager = tdbmanager::i();
    $dir = dirname(__file__) . '/sql/';
    $manager->CreateTable($self->table, file_get_contents($dir . 'post.sql'));
    $manager->CreateTable('pages', file_get_contents($dir . 'post.pages.sql'));
    $manager->CreateTable($self->rawtable, file_get_contents($dir . 'post.raw.sql'));

    $Cron = tcron::i();
    $Cron->add('hour', get_class($self) , 'HourCron');
}

function tpostsUninstall($self) {
    if ('litepubl\tposts' != get_class($self)) return;

    $Cron = tcron::i();
    $Cron->deleteclass(get_class($self));

    $widgets = twidgets::i();
    $widgets->deleteclass($self);
}

function tpostsGetsitemap($self, $from, $count) {
    $result = array();
    $commentpages = litepubl::$options->commentpages;
    $commentsperpage = litepubl::$options->commentsperpage;

    $db = $self->db;
    $now = sqldate();
    $res = $db->query("select $db->posts.title, $db->posts.pagescount, $db->posts.commentscount, $db->urlmap.url
  from $db->posts, $db->urlmap
  where $db->posts.status = 'published' and $db->posts.posted < '$now' and $db->urlmap.id = $db->posts.idurl
  order by $db->posts.posted desc limit $from, $count");
    while ($item = $db->fetchassoc($res)) {
        $comments = $commentpages ? ceil($item['commentscount'] / $commentsperpage) : 1;
        $result[] = array(
            'url' => $item['url'],
            'title' => $item['title'],
            'pages' => max($item['pagescount'], $comments)
        );
    }
    return $result;
}