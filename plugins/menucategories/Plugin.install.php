<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\menucategories;

use litepubl\admin\views\Schemes;
use litepubl\tag\Cats;

function PluginInstall($self)
{
    $categories = Cats::i();
    $categories->changed = $self->buildTree;
    $self->buildTree();

    Schemes::replaceMenu('litepubl\pages\Menus', get_class($self));
}

function PluginUninstall($self)
{
    Schemes::replaceMenu(get_class($self), 'litepubl\pages\Menus');

    $categories = Cats::i();
    $categories->unbind($self);
}
