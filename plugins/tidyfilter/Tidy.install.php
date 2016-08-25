<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\plugins\tidyfilter;

use litepubl\view\Filter;

function TtidyInstall($self)
{
    if (!class_exists('tidy')) {
        die('PHP tidy extension is required');
    }
    $filter = Filter::i();
    $filter->lock();
    $filter->onaftersimple = $self->filter;
    $filter->onaftercomment = $self->filter;
    $filter->unlock();
}

function TidyUninstall($self)
{
    $filter = Filter::i();
    $filter->unbind($self);
}
