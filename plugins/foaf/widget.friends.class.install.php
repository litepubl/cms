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

function tfriendswidgetInstall($self) {
     $self->getApp()->router->add($self->redirlink, get_class($self) , false, 'get');
     $self->getApp()->classes->add('tadminfriendswidget', 'admin.widget.friends.class.php', Plugins::getname(__file__));
    $self->addtosidebar(0);
}

function tfriendswidgetUninstall($self) {
     $self->getApp()->router->unbind($self);
     $self->getApp()->classes->delete('tadminfriendswidget');
}