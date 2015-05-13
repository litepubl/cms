<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function ttagreplacerInstall($self) {
  tthemeparser::i()->parsed = $self->themeparsed;
  ttheme::clearcache();
}

function ttagreplacerUninstall($self) {
  tthemeparser::i()->unbind($self);
  ttheme::clearcache();
}