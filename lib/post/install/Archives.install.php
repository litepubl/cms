<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\post;

use litepubl\widget\Widgets;

function ArchivesInstall($self)
{
    $posts = Posts::i();
    $posts->changed = $self->postsChanged;
}

function ArchivesUninstall($self)
{
    $self->getApp()->router->unbind($self);
    Posts::unsub($self);
    $widgets = Widgets::i();
    $widgets->deleteClass(get_class($self));
}

function ArchivesGetSitemap($self, $from, $count)
{
    $result = array();
    foreach ($self->items as $date => $item) {
        $result[] = array(
            'url' => $item['url'],
            'title' => $item['title'],
            'pages' => 1
        );
    }
    return $result;
}
