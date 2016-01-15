<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function titemsreplacerInstall($self) {
  $dir = basename(dirname(__file__));
  litepublisher::$classes->add('tviewthemereplacer', 'themereplacer.class.php', $dir);
  litepublisher::$classes->add('tthemereplacer', 'themereplacer.class.php', $dir);

  $views = tviews::i();
  $views->lock();
  $view = new tviewthemereplacer();
  $about = tplugins::getabout($dir);
  $view->name = $about['newview'];
  $id = $views->addview($view);
  $self->add($id);
  $view->themename = tview::i(1)->themename;
  $views->deleted = $self->delete;
  $views->unlock();

  ttheme::clearcache();
}

function titemsreplacerUninstall($self) {
  $views = tviews::i();
  $views->lock();
  foreach ($views->items as $id => & $item) {
    if ('tviewthemereplacer' == $item['class']) $item['class'] = 'tview';
  }
  $views->unbind($self);
  $views->unlock();

  litepublisher::$classes->delete('tviewthemereplacer');
  litepublisher::$classes->delete('tthemereplacer');

  ttheme::clearcache();
}