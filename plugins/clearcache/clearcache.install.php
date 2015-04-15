<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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