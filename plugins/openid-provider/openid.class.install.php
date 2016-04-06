<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function topenidInstall($self) {
    litepubl::$urlmap->add($self->url, get_class($self) , null, 'get');

    $template = ttemplate::i();
    $template->addtohead($self->get_head());

    $merger = tlocalmerger::i();
    $merger->addplugin(tplugins::getname(__file__));
}

function topenidUninstall($self) {
    turlmap::unsub($self);
    $template = ttemplate::i();
    $template->deletefromhead($self->get_head());

    $merger = tlocalmerger::i();
    $merger->deleteplugin(tplugins::getname(__file__));

    litepubl::$urlmap->clearcache();
}