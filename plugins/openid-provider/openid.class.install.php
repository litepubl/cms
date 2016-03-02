<?php

function topenidInstall($self) {
    litepublisher::$urlmap->add($self->url, get_class($self) , null, 'get');

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

    litepublisher::$urlmap->clearcache();
  }