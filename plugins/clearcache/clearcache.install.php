<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function tclearcacheInstall($self) {
  litepublisher::$urlmap->beforerequest = $self->clearcache;
  $parser = tthemeparser::i();
  $parser->parsed = $self->themeparsed;
}

function tclearcacheUninstall($self) {
  litepublisher::$urlmap->unbind($self);
  $parser = tthemeparser::i();
  $parser->unbind($self);
}