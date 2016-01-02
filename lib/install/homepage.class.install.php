<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function thomepageInstall($self) {
  litepublisher::$site->home = '/';
  $menus = tmenus::i();
  $menus->lock();
  $self->lock();
  $self->url = '/';
  $self->title = tlocal::i()->home;
  $self->idview = tviews::i()->add(tlocal::get('adminmenus', 'home'));
  $homeview = tview::i($self->idview);
  $homeview->disableajax = true;
  $homeview->save();
  
  $menus->idhome = $menus->add($self);
  $self->unlock();
  $menus->unlock();
  
  tposts::i()->addevent('changed', get_class($self), 'postschanged');
}

function thomepageUninstall($self) {
  turlmap::unsub($self);
  tposts::unsub($self);
  
  $menus = tmenus::i();
  $menus->lock();
  unset($menus->items[$menus->idhome]);
  $menus->sort();
  $menus->unlock();
}