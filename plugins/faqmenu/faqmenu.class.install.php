<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\core\Plugins;

function tfaqmenuInstall($self) {
    $about = Plugins::getabout(Plugins::getname(__file__));
    $self->title = $about['title'];
    $self->content = $about['content'];
    $menus = tmenus::i();
    $menus->add($self);
}

function tfaqmenuUninstall($self) {
    $menus = tmenus::i();
    $menus->lock();
    while ($id = $menus->class2id(get_class($self))) $menus->delete($id);
    $menus->unlock();
}