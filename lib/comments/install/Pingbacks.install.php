<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\comments;

use litepubl\post\Posts;

function PingbacksInstall($self)
{
    $manager = $self->db->man;
    $manager->CreateTable($self->table, file_get_contents(__DIR__ . '/sql/pingbacks.sql'));

    $posts = Posts::i();
    $posts->deleted = $self->postdeleted;
}

function PingbacksUninstall($self)
{
    Posts::unsub($self);
}