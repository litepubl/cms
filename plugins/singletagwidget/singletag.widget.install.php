<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tsingletagwidgetInstall($self) {
  $widgets = twidgets::i();
  $widgets->deleted = $self->widgetdeleted;

  $self->tags->deleted = $self->tagdeleted;
}

function tsingletagwidgetUninstall($self) {
  $self->tags->unbind($self);
}