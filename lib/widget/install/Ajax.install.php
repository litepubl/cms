<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\widget;
use litepubl\pages\RobotsTxt;

function AjaxInstall($self) {
     $self->getApp()->router->addget('/getwidget.htm', get_class($self));
    $robot  = RobotsTxt::i();
    $robot->AddDisallow('/getwidget.htm');
}

function AjaxUninstall($self) {
     $self->getApp()->router->unbind($self);
}
