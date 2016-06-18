<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\comments;

use litepubl\post\Posts;

function CommentsInstall($self)
{
    $manager = $self->db->man;
    $dir = dirname(__file__) . '/sql/';
    $manager->CreateTable($self->table, file_get_contents($dir . 'comments.sql'));
    $manager->CreateTable($self->rawtable, file_get_contents($dir . 'raw.sql'));

    Posts::i()->deleted = $self->postdeleted;
}

function CommentsUninstall($self)
{
    Posts::unsub($self);
}
