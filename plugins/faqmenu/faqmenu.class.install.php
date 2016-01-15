<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function tfaqmenuInstall($self) {
  $about = tplugins::getabout(tplugins::getname(__file__));
  $self->title = $about['title'];
  $self->content = $about['content'];
  $menus = tmenus::i();
  $menus->add($self);
}

function tfaqmenuUninstall($self) {
  $menus = tmenus::i();
  $menus->lock();
  while ($id = $menus->class2id(get_class($self))) $menus->delete($id);
  $menus->unlock();
}