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

function tcategoriesmenuInstall($self)
{
    $categories = tcategories::i();
    $categories->changed = $self->buildtree;
    $self->buildtree();

    tadminviews::replacemenu('tmenus', get_class($self));
}

function tcategoriesmenuUninstall($self)
{
    tadminviews::replacemenu(get_class($self) , 'tmenus');

    $categories = tcategories::i();
    $categories->unbind($self);
}

