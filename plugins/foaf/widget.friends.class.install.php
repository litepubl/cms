<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tfriendswidgetInstall($self) {
  litepubl::$urlmap->add($self->redirlink, get_class($self) , false, 'get');
  litepubl::$classes->add('tadminfriendswidget', 'admin.widget.friends.class.php', tplugins::getname(__file__));
  $self->addtosidebar(0);
}

function tfriendswidgetUninstall($self) {
  turlmap::unsub($self);
  litepubl::$classes->delete('tadminfriendswidget');
}