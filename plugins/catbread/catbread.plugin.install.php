<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function catbreadInstall($self) {
  $self->cats->onbeforecontent = $self->beforecat;
  $parser = tthemeparser::i();
$parser->lock();
$parser->parsed = $self->themeparsed;
$parser->addtags('plugins/catbread/resource/theme.txt', 'plugins/catbread/resource/theme.ini');
$parser->unlock();
}

function catbreadUninstall($self) {
  $self->cats->unbind($self);
  $parser = tthemeparser::i();
$parser->lock();
$parser->unbind($self);
$parser->removetags('plugins/catbread/resource/theme.txt', 'plugins/catbread/resource/theme.ini');
$parser->unlock();
}