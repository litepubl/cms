<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function likebuttonsInstall($self) {
  $name = basename(dirname(__file__));
  $js = tjsmerger::i();
  $js->lock();
  $js->add('default', "plugins/$name/resource/likebuttons.min.js");

  $js->addtext('default', 'facebook_appid', ";ltoptions.facebook_appid='$self->facebook_appid';");

  $js->unlock();
}

function likebuttonsUninstall($self) {
  $name = basename(dirname(__file__));
  $js = tjsmerger::i();
  $js->lock();
  $js->deletefile('default', "plugins/$name/resource/likebuttons.min.js");

  $js->deletetext('default', 'facebook_appid');
  $js->unlock();
}