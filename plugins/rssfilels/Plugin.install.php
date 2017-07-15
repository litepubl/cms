<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\rssfiles;

use litepubl\post\Rss;

function PluginInstall($self)
{
    $rss = Rss::i();
    $rss->beforepost = $self->beforePost;

    $self->getApp()->cache->clear();
}

function PluginUninstall($self)
{
    $rss = Rss::i();
    $rss->unbind($self);

    $self->getApp()->cache->clear();
}
