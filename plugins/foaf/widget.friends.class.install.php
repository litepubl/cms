<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tfriendswidgetInstall($self) {
  litepublisher::$urlmap->add($self->redirlink, get_class($self), false, 'get');
  litepublisher::$classes->add('tadminfriendswidget', 'admin.widget.friends.class.php', tplugins::getname(__file__));
  $self->addtosidebar(0);
}

function tfriendswidgetUninstall($self) {
  turlmap::unsub($self);
  litepublisher::$classes->delete('tadminfriendswidget');
}