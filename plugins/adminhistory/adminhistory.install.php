<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminhistoryInstall($self) {
  $widgets = twidgets::i();
  $widgets->lock();
  $self->id = $widgets->add($self);
  $widgets->onadminlogged = $self->onsidebar;
  $widgets->unlock();
  $self->save();
}