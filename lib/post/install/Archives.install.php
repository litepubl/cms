<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\post;

function ArchivesInstall($self) {
    $posts = Posts::i();
    $posts->changed = $self->postschanged;
}

function ArchivesUninstall($self) {
     $self->getApp()->router->unbind($self);
    Posts::unsub($self);
    $widgets = twidgets::i();
    $widgets->deleteclass(get_class($self));
}

function tarchivesGetsitemap($self, $from, $count) {
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