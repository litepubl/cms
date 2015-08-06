<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function likebuttonsInstall($self) {
  $name = basename(dirname(__file__));
  $js = tjsmerger::i();
  $js->lock();
  $js->add('default', "plugins/$name/resource/likebuttons.min.js");
  $js->unlock();
  
  tcssmerger::i()->add('default', 'js/litepubl/common/css/odnoklassniki.min.css');
}

function likebuttonsUninstall($self) {
  $name = basename(dirname(__file__));
  $js = tjsmerger::i();
  $js->lock();
  $js->deletefile('default', "plugins/$name/resource/likebuttons.min.js");
  $js->unlock();
  
  //stay
  //tcssmerger::i()->deletefile('default', 'js/litepubl/common/css/odnoklassniki.min.css');
}