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
uuse litepubl;

function AjaxInstall($self) {
litepubl::$app->router->addget($self->url, get_class($self));
    $robot  = RobotsTxt::i();
    $robot->AddDisallow($self->url);
}

function AjaxUninstall($self) {
litepubl::$app->router->unbind($self);
}
