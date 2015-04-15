<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tviewsInstall($self) {
  $widgets = twidgets::i();
  $widgets->deleted = $self->widgetdeleted;
  
  $self->lock();
  $lang = tlocal::admin('names');
  $default = $self->add($lang->default);
  $def = tview::i($default);
  $def->sidebars = array(array(), array(), array());
  
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