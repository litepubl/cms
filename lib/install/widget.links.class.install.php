<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tlinkswidgetInstall($self) {
  if (get_class($self) != 'tlinkswidget') return;
  tlocal::usefile('admin');
  $lang = tlocal::i('installation');
  $self->add($lang->homeurl, $lang->homedescription, $lang->homename);

  $urlmap = turlmap::i();
  $urlmap->add($self->redirlink, get_class($self) , null, 'get');

  $robots = trobotstxt::i();
  $robots->AddDisallow($self->redirlink);
  $robots->save();
}

function tlinkswidgetUninstall($self) {
  if (get_class($self) != 'tlinkswidget') return;
  turlmap::unsub($self);
}