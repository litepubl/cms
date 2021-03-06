<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\shortcode;

use litepubl\view\Filter;

function PluginInstall($self)
{
    $filter = Filter::i();
    $filter->lock();
    $filter->beforefilter = $self->filter;
    $filter->oncomment = $self->filter;
    $filter->unlock();
}

function PluginUninstall($self)
{
    Filter::i()->unbind($self);
}
