<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function uloginInstall($self) {
  $self->data['nets'] = array('vkontakte', 'odnoklassniki', 'mailru', 'facebook', 'twitter', 'google', 'yandex', 'livejournal', 'openid', 'flickr', 'lastfm', 'linkedin', 'liveid', 'soundcloud', 'steam', 'vimeo', 'webmoney', 'youtube', 'foursquare', 'tumblr', 'googleplus');
  
  $man = tdbmanager::i();
  $man->createtable($self->table, str_replace('$names', implode("', '", $self->data['nets']), file_get_contents(dirname(__file__) . '/resource/ulogin.sql')));
  if (!$man->column_exists('users', 'phone')) $man->alter('users', "add phone bigint not null default '0' after status");
  tusers::i()->deleted = $self->userdeleted;
  
  $lang = tplugins::getnamelang(basename(dirname(__file__)));
  
  $self->panel = '<h4>' . $lang->panel_title . '</h4>
  <div id="uLogin" data-ulogin="display=small;fields=first_name,last_name;optional=email,phone,nickname;providers=vkontakte,odnoklassniki,mailru,yandex,facebook,google,twitter;hidden=other;redirect_uri=' .
  urlencode(litepublisher::$site->url . $self->url . '?backurl=') . ';"></div>
  <script type="text/javascript">
  $.ready2(function() {
    litepubl.ulogin.ready();
  });
  </script>';
  
  $self->button = '<div class="center-block"><button type="button" class="btn btn-default" id="ulogin-comment-button">' . $lang->button_title . '</button></div>';
  
  $self->save();
  
  $alogin = tadminlogin::i();
  $alogin ->widget = $self->addpanel($alogin ->widget, $self->panel);
  $alogin->save();
  
  $areg = tadminreguser::i();
  $areg->widget = $self->addpanel($areg->widget, $self->panel);
  $areg->save();
  
  $tc = ttemplatecomments::i();
  $tc->regaccount = $self->addpanel($tc->regaccount, $self->button);
  $tc->save();
  
  litepublisher::$urlmap->addget($self->url, get_class($self));
  
  $js = tjsmerger::i();
  $js->lock();
  $js->add('default', '/plugins/ulogin/resource/ulogin.popup.min.js');
  $js->add('default', '/plugins/ulogin/resource/' . litepublisher::$options->language . '.ulogin.popup.min.js');
  litepublisher::$classes->add('emailauth', 'emailauth.class.php', 'ulogin');
  $js->unlock();
  
  tcssmerger::i()->add('default', '/plugins/ulogin/resource/ulogin.popup.css');
  
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
  $alogin ->widget = $self->deletepanel($alogin ->widget);
  $alogin->save();
  
  $areg = tadminreguser::i();
  $areg->widget = $self->deletepanel($areg->widget);
  $areg->save();
  
  $tc = ttemplatecomments::i();
  $tc->regaccount = $self->deletepanel($tc->regaccount);
  $tc->save();
  
  $js = tjsmerger::i();
  $js->lock();
  $js->deletefile('default', '/plugins/ulogin/resource/ulogin.popup.min.js');
  $js->deletefile('default', '/plugins/ulogin/resource/' . litepublisher::$options->language . '.ulogin.popup.min.js');
  $js->unlock();
  
  tcssmerger::i()->deletefile('default', '/plugins/ulogin/resource/ulogin.popup.css');
  
  tjsonserver::i()->unbind($self);
  
  litepublisher::$classes->delete('emailauth');
}