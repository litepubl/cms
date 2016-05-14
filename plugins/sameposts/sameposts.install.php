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

use litepubl\core\DBManager;

function tsamepostsInstall($self)
{
    $manager = DBManager::i();
    $manager->createtable($self->table, 'id int UNSIGNED NOT NULL default 0,
    items text NOT NULL,
    PRIMARY KEY(id) ');

    $widgets = twidgets::i();
    $widgets->addclass($self, 'tpost');

    $posts = tposts::i();
    $posts->changed = $self->postschanged;
}

function tsamepostsUninstall($self)
{
    tposts::unsub($self);
    twidgets::i()->deleteclass(get_class($self));

    $manager = DBManager::i();
    $manager->deletetable($self->table);

    $posts = tposts::i();
    $dir = $self->getApp()->paths->data . 'posts' . DIRECTORY_SEPARATOR;
    foreach ($posts->items as $id => $item) {
        @unlink($dir . $id . DIRECTORY_SEPARATOR . 'same.php');
    }
}
}

