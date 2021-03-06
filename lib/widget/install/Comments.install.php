<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\widget;

use litepubl\comments\Comments as ComItems;

function CommentsInstall($self)
{
    ComItems::i()->changed = $self->changed;
}

function CommentsUninstall($self)
{
    ComItems::i()->unbind($self);
}
