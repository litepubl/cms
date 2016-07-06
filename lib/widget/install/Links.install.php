<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\widget;

use litepubl\pages\RobotsTxt;
use litepubl\view\Lang;

function LinksInstall($self)
{
    if (get_class($self) != __NAMESPACE__ . '\Links') {
        return;
    }

    Lang::usefile('admin');
    $lang = Lang::i('installation');
    $self->add($lang->homeurl, $lang->homedescription, $lang->homename);

    $self->getApp()->router->add($self->redirlink, get_class($self), null, 'get');

    $robots = RobotsTxt::i();
    $robots->AddDisallow($self->redirlink);
    $robots->save();
}

function LinksUninstall($self)
{
    if (get_class($self) != __NAMESPACE__ . '\Links') {
        return;
    }

    $self->getApp()->router->unbind($self);
}
