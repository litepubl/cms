<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\pages;

function JsonInstall($self)
{
    $self->getApp()->router->addget($self->url, get_class($self));
}

function JsonUninstall($self)
{
    $self->getApp()->router->unbind($self);
}
