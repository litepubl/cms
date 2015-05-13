<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
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