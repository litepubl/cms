<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function ttagreplacerInstall($self) {
  tthemeparser::i()->parsed = $self->themeparsed;
  ttheme::clearcache();
}

function ttagreplacerUninstall($self) {
  tthemeparser::i()->unbind($self);
  ttheme::clearcache();
}