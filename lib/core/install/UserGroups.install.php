<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\core;

use litepubl\view\Lang;

function UserGroupsInstall($self)
{
    lang::usefile('install');
    $lang = lang::i('initgroups');
    $self->lock();
    $admin = $self->add('admin', $lang->admin, '/admin/');
    $editor = $self->add('editor', $lang->editor, '/admin/posts/');
    $author = $self->add('author', $lang->author, '/admin/posts/');
    $moder = $self->add('moderator', $lang->moderator, '/admin/comments/');
    $commentator = $self->add('commentator', $lang->commentator, '/admin/comments/');

    $self->items[$author]['parents'] = [
        $editor
    ];
    $self->items[$commentator]['parents'] = [
        $moder,
        $author
    ];

    $self->unlock();
}
