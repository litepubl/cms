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

function ttidyfilterInstall($self)
{
    if (!class_exists('tidy')) die('PHP tidy extension is required');
    $filter = Filter::i();
    $filter->lock();
    $filter->onaftersimple = $self->filter;
    $filter->onaftercomment = $self->filter;
    $filter->unlock();
}

function ttidyfilterUninstall($self)
{
    $filter = Filter::i();
    $filter->unbind($self);
}

