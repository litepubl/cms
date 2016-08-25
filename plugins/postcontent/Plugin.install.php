<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\plugins\postcontent;

use litepubl\post\View;

function PluginInstall($self)
{
    $view = View::i();
    $view->lock();
    $view->beforecontent = $self->beforeContent;
    $view->aftercontent = $self->afterContent;
    $view->unlock();
}

function PluginUninstall($self)
{
    View::unsub($self);
}
