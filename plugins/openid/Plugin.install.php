<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\plugins\openid;

use litepubl\core\Plugins;
use litepubl\view\LangMerger;
use litepubl\view\MainView;

function PluginInstall($self)
{
    $self->getApp()->router->add($self->url, get_class($self), null, 'get');

    $template = MainView::i();
    $template->addtohead($self->get_head());

    $merger = LangMerger::i();
    $merger->addplugin(Plugins::getname(__file__));
}

function PluginUninstall($self)
{
    $self->getApp()->router->unbind($self);
    $template = MainView::i();
    $template->deletefromhead($self->get_head());

    $merger = LangMerger::i();
    $merger->deleteplugin(Plugins::getname(__file__));

    $self->getApp()->cache->clear();
}
