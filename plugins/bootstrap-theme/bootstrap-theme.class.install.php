<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function bootstrap_themeInstall($self) {
  $js = tjsmerger::i();
  $js->lock();
  
  tplugins::i()->add('rss-chrome');
  $js->unlock();
  
  $css = tcssmerger::i();
  $css->lock();
  $css->deletefile('default', '/js/prettyphoto/css/prettyPhoto.css');
  $css->deletefile('default', '/js/litepublisher/css/prettyphoto.dialog.min.css');
  $css->deletefile('default', '/js/litepublisher/css/button.min.css');
  $css->unlock();

$appcache = appcache_manifest::i();
$appcache->lock();
$appcache->add('/themes/shop/css/$template.themecolor.min.css');
$appcache->add('/themes/shop/fonts/lobster.woff');
$appcache->add('/themes/shop/css/font-awesome.min.css');
$appcache->add('/themes/shop/fonts/fontawesome-webfont.woff');
$appcache->unlock();

  ttheme::clearcache();
}

function bootstrap_themeUninstall($self) {
  $t = ttemplate::i();
  unset($t->data['themecolor']);
  $t->save();
  
  $js = tjsmerger::i();
  $js->lock();
  $js->unlock();
  
  $css = tcssmerger::i();
  $css->lock();
  $css->add('default', '/js/prettyphoto/css/prettyPhoto.css');
  $css->add('default', '/js/litepublisher/css/prettyphoto.dialog.min.css');
  $css->add('default', '/js/litepublisher/css/button.min.css');
  $css->unlock();

$appcache = appcache_manifest::i();
$appcache->lock();
$appcache->delete('/themes/shop/css/$template.themecolor.min.css');
$appcache->delete('/themes/shop/fonts/lobster.woff');
$appcache->delete('/themes/shop/css/font-awesome.min.css');
$appcache->delete('/themes/shop/fonts/fontawesome-webfont.woff');
$appcache->unlock();

  ttheme::clearcache();
}