<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\widget;

use litepubl;
use litepubl\pages\RobotsTxt;

function AjaxInstall($self)
{
    litepubl::$app->router->addget($self->url, get_class($self));
    $robot = RobotsTxt::i();
    $robot->AddDisallow($self->url);
}

function AjaxUninstall($self)
{
    litepubl::$app->router->unbind($self);
}
