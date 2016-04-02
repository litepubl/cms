<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tviewsInstall($self) {
  $widgets = twidgets::i();
  $widgets->deleted = $self->widgetdeleted;

  $self->lock();
  $lang = tlocal::admin('names');
  $default = $self->add($lang->default);
  $def = tview::i($default);
  $def->sidebars = array(
    array() ,
    array() ,
    array()
  );

  $idadmin = $self->add($lang->adminpanel);
  $admin = tview::i($idadmin);
  $admin->menuclass = 'tadminmenus';

  $self->defaults = array(
    'post' => $default,
    'menu' => $default,
    'category' => $default,
    'tag' => $default,
    'admin' => $idadmin
  );

  $self->unlock();
}