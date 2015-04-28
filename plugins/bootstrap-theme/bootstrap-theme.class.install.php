<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function bootstrap_themeInstall($self) {
  $lang = tlocal::inicache(dirname(dirname(__file__)) . '/adminmenus/resource/' . litepublisher::$options->language . '.install.ini');
  $lang->firstsearch('shopmenus', 'shop', 'adminshop');
  
  $names = array(
  'tmenus' => 'shop',
  'adminshopmenus' => 'adminshop',
  'cabinetmenus' => 'cabinet',
  );
  
  $views = tviews::i();
  $views->lock();
  foreach($names as $menuclass => $name) {
    $title = $lang->__get($name);
    if (!($view = $views->get($title))) {
      $view = tview::i($views->add($title));
    }
    
    $view->menuclass = $menuclass;
    $views->defaults[$name] = $view->id;
  }
  
  foreach ($views->items as $idview => $viewitem) {
    $view = tview::i($idview);
    $view->themename = 'shop';
    $view->  disableajax = false;
  }
  $views->unlock();
  
  $t = ttemplate::i();
  $t->data['themecolor'] = 'default';
  $t->save();
  
  ttheme::clearcache();
  $js = tjsmerger::i();
  $js->lock();
  $js->add('default', '/themes/shop/js/transition.min.js');
  $js->add('default', '/themes/shop/js/collapse.min.js');
  $js->add('default', '/themes/shop/js/dropdown.min.js');
  $js->add('default', '/themes/shop/js/modal.min.js');
  $js->add('default', '/themes/shop/js/tooltip.min.js');
  $js->add('default', '/themes/shop/js/popover.min.js');
  $js->add('default', '/themes/shop/js/poppost.bootstrap.min.js');
  $js->add('default', '/themes/shop/js/theme.min.js');
  $js->add('default', '/js/plugins/fontfaceobserver.standalone.js');
  $js->add('default', '/themes/shop/js/lobster.min.js');
  $js->add('default', '/themes/shop/js/font-awesome.min.js');
  $js->add('default', '/js/litepublisher/popimage.bootstrap.min.js');
  $js->add('default', '/js/litepublisher/youtube.bootstrap.min.js');
  
  $js->deletefile('default', '/js/prettyphoto/js/jquery.prettyPhoto.js');
  $js->deletefile('default', '/js/litepublisher/dialog.pretty.min.js');
  $js->deletefile('default', '/js/litepublisher/pretty.init.min.js');
  $js->deletefile('default', '/js/litepublisher/youtubefix.min.js');
  
  $js->add('home', '/themes/shop/js/jquery.jcarousel-core.min.js');
  $js->add('home', '/themes/shop/js/jquery.jcarousel-autoscroll.min.js');
  $js->add('home', '/themes/shop/js/modernizr.transitions.min.js');
  $js->add('home', '/themes/shop/js/homecarousel.min.js');
  
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
}

function bootstrap_themeUninstall($self) {
  $t = ttemplate::i();
  unset($t->data['themecolor']);
  $t->save();
  
  $parser = tthemeparser::i();
  $parser->lock();
  $parser->unbind($self);
  foreach ($parser->extrapaths as $key => $args) {
    if (strbegin($key, 'shop.')) unset($parser->extrapaths[$key]);
  }
  $parser->unlock();
  
  $views = tviews::i();
  foreach ($views->items as $idview => $viewitem) {
    tview::i($idview)->themename = 'default';
  }
  $views->save();
  
  ttheme::clearcache();
  
  $js = tjsmerger::i();
  $js->lock();
  $js->deletefile('default', '/themes/shop/js/transition.min.js');
  $js->deletefile('default', '/themes/shop/js/collapse.min.js');
  $js->deletefile('default', '/themes/shop/js/dropdown.min.js');
  $js->deletefile('default', '/themes/shop/js/modal.min.js');
  $js->deletefile('default', '/themes/shop/js/tooltip.min.js');
  $js->deletefile('default', '/themes/shop/js/popover.min.js');
  $js->deletefile('default', '/themes/shop/js/poppost.bootstrap.min.js');
  $js->deletefile('default', '/themes/shop/js/theme.min.js');
  $js->deletefile('default', '/js/plugins/fontfaceobserver.standalone.js');
  $js->deletefile('default', '/themes/shop/js/lobster.min.js');
  $js->deletefile('default', '/themes/shop/js/font-awesome.min.js');
  $js->deletefile('default', '/js/litepublisher/popimage.bootstrap.min.js');
  $js->deletefile('default', '/js/litepublisher/youtube.bootstrap.min.js');
  
  $js->after('default', '/js/jquery/jquery-$site.jquery_version.min.js', '/js/prettyphoto/js/jquery.prettyPhoto.js');
  $js->add('default', '/js/litepublisher/dialog.pretty.min.js');
  $js->add('default', '/js/litepublisher/pretty.init.min.js');
  $js->add('default', '/js/litepublisher/youtubefix.min.js');
  
  $js->deletesection('home');
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
}