<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

use litepubl\view\Filter;

function ttoptextinstall($self)
{
    $filter = Filter::i();
    $filter->lock();
    $filter->beforecontent = $self->beforecontent;
    $filter->aftercontent = $self->aftercontent;
    $filter->unlock();
}

function ttoptextuninstall($self)
{
    $filter = Filter::i();
    $filter->unbind($self);
}

