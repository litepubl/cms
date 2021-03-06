<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\toptext;

use litepubl\view\Filter;

function TopTextInstall($self)
{
    $filter = Filter::i();
    $filter->lock();
    $filter->beforecontent = $self->beforeContent;
    $filter->aftercontent = $self->afterContent;
    $filter->unlock();
}

function TopTextUninstall($self)
{
    $filter = Filter::i();
    $filter->unbind($self);
}
