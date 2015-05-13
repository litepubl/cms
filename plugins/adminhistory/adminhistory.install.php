<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tadminhistoryInstall($self) {
  $widgets = twidgets::i();
  $widgets->lock();
  $self->id = $widgets->add($self);
  $widgets->onadminlogged = $self->onsidebar;
  $widgets->unlock();
  $self->save();
}