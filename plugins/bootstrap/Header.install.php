<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\bootstrap;

use litepubl\admin\Menus;
use litepubl\core\Plugins;

function HeaderInstall($self)
{
    $about = Plugins::getabout(Plugins::getname(__file__));

    $admin = Menus::i();
    $admin->additem(
        [
        'parent' => $admin->url2id('/admin/views/') ,
        'url' => '/admin/views/bootstrapheader/',
        'title' => $about['header'],
        'name' => 'bootstrapheader',
        'class' => get_class($self) ,
        'group' => 'admin'
        ]
    );
}

function HeaderUninstall($self)
{
    Menus::i()->deleteUrl('/admin/views/bootstrapheader/');
}
