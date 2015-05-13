<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tsubcatwidgetInstall($self) {
  $widgets = twidgets::i();
  $widgets->deleted = $self->widgetdeleted;
  
  $self->tags->deleted = $self->tagdeleted;
}

function tsubcatwidgetUninstall($self) {
  $self->tags->unbind($self);
}