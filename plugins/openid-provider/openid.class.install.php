<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\LangMerger;
use litepubl\core\Plugins;

function topenidInstall($self) {
     $self->getApp()->router->add($self->url, get_class($self) , null, 'get');

    $template = ttemplate::i();
    $template->addtohead($self->get_head());

    $merger = LangMerger::i();
    $merger->addplugin(Plugins::getname(__file__));
}

function topenidUninstall($self) {
     $self->getApp()->router->unbind($self);
    $template = ttemplate::i();
    $template->deletefromhead($self->get_head());

    $merger = LangMerger::i();
    $merger->deleteplugin(Plugins::getname(__file__));

     $self->getApp()->cache->clear();
}