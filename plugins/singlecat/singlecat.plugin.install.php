<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function tsinglecatInstall($self) {
  if (!dbversion) die('Required database version');
  tthemeparser::i()->parsed = $self->themeparsed;
  ttheme::clearcache();
}

function tsinglecatUninstall($self) {
  tthemeparser::i()->unbind($self);
  ttheme::clearcache();
}