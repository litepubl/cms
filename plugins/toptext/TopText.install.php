<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\plugins\toptext;

use litepubl\view\Filter;

function TopTextinstall($self)
{
    $filter = Filter::i();
    $filter->lock();
    $filter->beforecontent = $self->beforeContent;
    $filter->aftercontent = $self->afterContent;
    $filter->unlock();
}

function TopTextuninstall($self)
{
    $filter = Filter::i();
    $filter->unbind($self);
}
