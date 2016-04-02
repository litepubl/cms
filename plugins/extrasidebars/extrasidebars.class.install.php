<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl\plugins;
use litepubl;

function textrasidebarsInstall($self) {
  $parser = tthemeparser::i();
  $parser->lock();
  $parser->onfix = $self->fix;
  $parser->parsed = $self->themeparsed;
  $parser->unlock();

  ttheme::clearcache();
}

function textrasidebarsUninstall($self) {
  tthemeparser::i()->unbind($self);
  ttheme::clearcache();
}