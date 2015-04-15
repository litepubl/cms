<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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