<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\comments;

use litepubl\core\users;
use litepubl\post\Posts;

function SubscribersInstall($self)
{
    $dbmanager = $self->db->man;
    $dbmanager->CreateTable($self->table, file_get_contents(dirname(dirname(__DIR__)) . '/core/install/sql/ItemsPosts.sql'));

    $self->fromemail = 'litepublisher@' . $_SERVER['HTTP_HOST'];
    $self->save();

    $posts = Posts::i();
    $posts->added = $self->postAdded;
    $posts->deleted = $self->postDeleted;

    $comments = Comments::i();
    $comments->lock();
    $comments->added = $self->commentAdded;
    $comments->onapproved = $self->commentAdded;
    $comments->unlock();

    Users::i()->deleted = $self->itemDeleted;
}

function SubscribersUninstall($self)
{
    Comments::i()->unbind($self);
    Users::i()->unbind($self);
    Posts::i()->unbind($self);
}
