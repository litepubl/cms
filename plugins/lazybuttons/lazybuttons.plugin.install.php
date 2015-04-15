<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tlazybuttonsInstall($self) {
  $about = tplugins::getabout(tplugins::getname(__file__));
  $o = array(
  'lang' => litepublisher::$options->language,
  'twituser' => '',
  'show' => $about['show'],
  'hide' =>  $about['hide']
  );
  
  $jsmerger = tjsmerger::i();
  $jsmerger->lock();
  $jsmerger->add('default', dirname(__file__) . '/lazybuttons.min.js');
  $jsmerger->addtext('default', 'lazybuttons',
  sprintf('var lazyoptions = %s;', json_encode($o)));
  $jsmerger->unlock();
  
  $parser = tthemeparser::i();
  $parser->parsed = $self->themeparsed;
  ttheme::clearcache();
}

function tlazybuttonsUninstall($self) {
  $jsmerger = tjsmerger::i();
  $jsmerger->lock();
  $jsmerger->deletefile('default', dirname(__file__) . '/lazybuttons.min.js');
  $jsmerger->deletetext('default', 'lazybuttons');
  $jsmerger->unlock();
  
  $parser = tthemeparser::i();
  $parser->unbind($self);
  ttheme::clearcache();
}