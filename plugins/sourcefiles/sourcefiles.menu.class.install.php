<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tsourcefilesmenuInstall($self) {
  $about = tplugins::localabout(dirname(__file__));
  $self->title = $about['title'];
  $self->url = '/source/';
  $menus = tmenus::i();
  $menus->add($self);
}

function tsourcefilesmenuUninstall($self) {
  $menus = tmenus::i();
  $menus->deleteurl('/source/');
}