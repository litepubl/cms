<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
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

