<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\comments;

use litepubl\pages\Json as Server;

function JsonInstall($self)
{
    $json = Server::i();
    $json->lock();
    $json->addevent('comment_delete', get_class($self), 'comment_delete');
    $json->addevent('comment_setstatus', get_class($self), 'comment_setstatus');
    $json->addevent('comment_edit', get_class($self), 'comment_edit');
    $json->addevent('comment_getraw', get_class($self), 'comment_getraw');
    $json->addevent('comments_get_hold', get_class($self), 'comments_get_hold');
    $json->addevent('comment_add', get_class($self), 'comment_add');
    $json->addevent('comment_confirm', get_class($self), 'comment_confirm');
    $json->addevent('comments_get_logged', get_class($self), 'comments_get_logged');
    $json->unlock();
}

function JsonUninstall($self)
{
    Server::i()->unbind($self);
}
