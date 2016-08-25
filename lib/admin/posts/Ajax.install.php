<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\admin\posts;

function AjaxInstall($self)
{
    $self->getApp()->router->addget('/admin/ajaxposteditor.htm', get_class($self));
}

function AjaxUninstall($self)
{
    $self->getApp()->router->unbind($self);
}
