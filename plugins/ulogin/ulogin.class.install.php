<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function uloginInstall($self) {
  $self->data['nets'] = array('vkontakte', 'odnoklassniki', 'mailru', 'facebook', 'twitter', 'google', 'yandex', 'livejournal', 'openid', 'flickr', 'lastfm', 'linkedin', 'liveid', 'soundcloud', 'steam', 'vimeo', 'webmoney', 'youtube', 'foursquare', 'tumblr', 'googleplus');
  
  $man = tdbmanager::i();
  $man->createtable($self->table, str_replace('$names', implode("', '", $self->data['nets']), file_get_contents(dirname(__file__) . '/resource/ulogin.sql')));
  if (!$man->column_exists('users', 'phone')) $man->alter('users', "add phone bigint not null default '0' after status");
  tusers::i()->deleted = $self->userdeleted;
  
  $alogin = tadminlogin::i();
  $alogin ->widget .= $self->panel;
  $alogin->save();
  
  $areg = tadminreguser::i();
  $areg->widget .= $self->panel;
  $areg->save();
  
  litepublisher::$urlmap->addget($self->url, get_class($self));
  
  $js = tjsmerger::i();
  $js->lock();
  $js->add('default', '/plugins/ulogin/resource/ulogin.popup.min.js');
  $js->add('default', '/plugins/ulogin/resource/' . litepublisher::$options->language . '.ulogin.popup.min.js');
  litepublisher::$classes->add('emailauth', 'emailauth.class.php', 'ulogin');

  $js->add('default', '/plugins/ulogin/resource/authdialog.min.js');
  $js->unlock();
  
  tcssmerger::i()->add('default', '/plugins/ulogin/resource/ulogin.popup.min.css');
  
  $json = tjsonserver::i();
  $json->lock();
  $json->addevent('ulogin_auth', get_class($self), 'ulogin_auth');
  $json->addevent('check_logged', get_class($self), 'check_logged');
  $json->unlock();
}

function uloginUninstall($self) {
  tusers::i()->unbind('tregserviceuser');
  turlmap::unsub($self);
  $man = tdbmanager::i();
  $man->deletetable($self->table);
  if ($man->column_exists('users', 'phone')) $man->alter('users', "drop phone");
  
  $alogin = tadminlogin::i();
  $alogin ->widget = str_replace($self->panel, '', $alogin ->widget);
  $alogin->save();
  
  $areg = tadminreguser::i();
  $areg->widget = str_replace($self->panel, '', $areg->widget);
  $areg->save();
  
    $js = tjsmerger::i();
  $js->lock();
  $js->deletefile('default', '/plugins/ulogin/resource/ulogin.popup.min.js');
  $js->deletefile('default', '/plugins/ulogin/resource/' . litepublisher::$options->language . '.ulogin.popup.min.js');
  $js->deletefile('default', '/plugins/ulogin/resource/authdialog.min.js');

  litepublisher::$classes->delete('emailauth');
  $js->unlock();
  
  tcssmerger::i()->deletefile('default', '/plugins/ulogin/resource/ulogin.popup.min.css');
  
  tjsonserver::i()->unbind($self);
}