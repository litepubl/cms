<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\blackip;

use litepubl\comments\Manager;

function BlackIPInstall($self)
{
    Manager::i()->oncreatestatus = $self->filter;
}

function BlackIPUninstall($self)
{
    Manager::i()->unbind($self);
}
