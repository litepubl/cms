<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\plugins\faqmenu;

use litepubl\core\Plugins;
use litepubl\pages\Menus;

function MenuInstall($self)
{
    $about = Plugins::getabout(Plugins::getname(__file__));
    $self->title = $about['title'];
    $self->content = $about['content'];
    $menus = Menus::i();
    $menus->add($self);
}

function MenuUninstall($self)
{
    $menus = Menus::i();
    $menus->lock();
    while ($id = $menus->class2id(get_class($self))) {
        $menus->delete($id);
    }
    $menus->unlock();
}
