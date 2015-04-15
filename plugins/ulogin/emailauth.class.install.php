<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function emailauthInstall($self) {
  $js = tjsmerger::i();
  $js->lock();
  $js->add('default', '/plugins/ulogin/resource/email.auth.min.js');
  $js->add('default', '/plugins/ulogin/resource/' . litepublisher::$options->language . '.email.auth.min.js');
  $js->unlock();
  
  $json = tjsonserver::i();
  $json->lock();
  $json->addevent('email_login', get_class($self), 'email_login');
  $json->addevent('email_reg', get_class($self), 'email_reg');
  $json->addevent('email_lostpass', get_class($self), 'email_lostpass');
  $json->unlock();
}

function emailauthUninstall($self) {
  $js = tjsmerger::i();
  $js->lock();
  $js->deletefile('default', '/plugins/ulogin/resource/email.auth.min.js');
  $js->deletefile('default', '/plugins/ulogin/resource/' . litepublisher::$options->language . '.email.auth.min.js');
  $js->unlock();
  
  tjsonserver::i()->unbind($self);
}