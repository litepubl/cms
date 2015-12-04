<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tcontactformInstall($self) {
  $html = tadminhtml::i();
  $html->loadinstall();
  $html->section = 'contactform';
  tlocal::usefile('install');
  $lang = tlocal::i('contactform');
  
  $self->title =  $lang->title;
  $self->subject = $lang->subject;
  $self->success  = $html->success();
  $self->errmesg = $html->errmesg();
  $self->content = $html->form();
  $self->order = 10;
  
  $menus = tmenus::i();
  $menus->add($self);
}

function tcontactformUninstall($self) {
  $menus = tmenus::i();
  $menus->delete($self->id);
}