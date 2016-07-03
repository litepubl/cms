<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
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

    $self->items[$author]['parents'] = array(
        $editor
    );
    $self->items[$commentator]['parents'] = array(
        $moder,
        $author
    );

    $self->unlock();
}
