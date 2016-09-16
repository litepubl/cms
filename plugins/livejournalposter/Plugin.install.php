<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\livejournalposter;

use litepubl\post\Posts;

function PluginInstall($self)
{
    $posts = Posts::i();
    $posts->singlecron = $self->sendPost;
}

function PluginUninstall($self)
{
    Posts::unsub($self);
}
