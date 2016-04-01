<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tredirectorInstall($self) {
  $self->lock();
  $self->add('/rss/', '/rss.xml');
  $self->add('/rss', '/rss.xml');
  $self->add('/feed/', '/rss.xml');
  $self->add('/wp-rss.php', '/rss.xml');
  $self->add('/wp-rss2.php', '/rss.xml');
  $self->add('/contact.php', '/kontakty.htm');
  $self->add('/kontakty.htm', '/contact.htm');
  $self->add('/wp-login.php', '/admin/login/');
  $self->unlock();
}