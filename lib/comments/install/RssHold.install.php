<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 */

namespace litepubl\comments;

function RssHoldInstall($self)
{
    $self->idurl = $self->getApp()->router->add($self->url, get_class($self), null, 'usernormal');

    $self->template = file_get_contents(dirname(dirname(__DIR__)) . '/install/templates/RssHold.tml');
    $self->save();

    Comments::i()->changed = $self->commentschanged;
}

function RssHoldUninstall($self)
{
    $self->getApp()->router->unbind($self);
    Comments::i()->unbind($self);
}
