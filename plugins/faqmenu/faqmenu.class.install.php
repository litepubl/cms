<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tfaqmenuInstall($self) {
  $about = tplugins::getabout(tplugins::getname(__file__));
  $self->title =  $about['title'];
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