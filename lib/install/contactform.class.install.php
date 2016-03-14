<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function tcontactformInstall($self) {
  $ini = parse_ini_file(dirname(__file__) . '/templates/contactform.ini');
  tlocal::usefile('install');
  $lang = tlocal::i('contactform');
  $theme = ttheme::i();

  $self->title = $lang->title;
  $self->subject = $lang->subject;
  $self->order = 10;
  $self->success = $theme->parse($ini['success']);
  $self->errmesg = $theme->parse($ini['errmesg']);
  $self->content = $theme->parse($ini['form']);

  $menus = tmenus::i();
  $menus->add($self);
}

function tcontactformUninstall($self) {
  $menus = tmenus::i();
  $menus->delete($self->id);
}