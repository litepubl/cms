<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\post;

function FilesInstall($self)
{
    $manager = $self->db->man;
    $dir = dirname(__file__) . '/sql/';
    $manager->createtable($self->table, file_get_contents($dir . 'files.sql'));
    $manager->createtable('imghashes', file_get_contents($dir . 'imghashes.sql'));

    $posts = Posts::i();
    $posts->lock();
    $posts->added = $self->postedited;
    $posts->edited = $self->postedited;
    $posts->unlock();
}

function FilesUninstall($self)
{
    Posts::unsub($self);
}
