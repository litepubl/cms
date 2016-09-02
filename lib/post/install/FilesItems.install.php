<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\post;

function FilesItemsInstall($self)
{
    $manager = $self->db->man;
    $manager->createtable($self->table, file_get_contents($self->getApp()->paths->lib . 'core/install/sql/ItemsPosts.sql'));

    $posts = Posts::i();
    $posts->deleted = $self->postDeleted;
}

function FilesitemsUninstall($self)
{
    Posts::unsub($self);
}
