<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpostsInstall($self) {
  if ('tposts' != get_class($self)) return;
  if (dbversion) {
    $manager = tdbmanager ::i();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'posts.sql'));
    $manager->CreateTable('pages', file_get_contents($dir .'postspages.sql'));
    $manager->CreateTable($self->rawtable, file_get_contents($dir .'raw.sql'));
  } else {
    $dir = litepublisher::$paths->data . 'posts';
    @mkdir($dir, 0777);
    @chmod($dir, 0777);
  }
  $Cron = tcron::i();
  $Cron->add('hour', get_class($self), 'HourCron');
}

function tpostsUninstall($self) {
  if ('tposts' != get_class($self)) return;
  $Cron = tcron::i();
  $Cron->deleteclass(get_class($self));
  
  $widgets = twidgets::i();
  $widgets->deleteclass($self);
  //@rmdir(litepublisher::$paths->data . 'posts');
}

function tpostsGetsitemap($self, $from, $count) {
  $result = array();
  $commentpages  = litepublisher::$options->commentpages;
  $commentsperpage = litepublisher::$options->commentsperpage;
  
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