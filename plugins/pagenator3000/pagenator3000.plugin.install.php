<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpagenator3000Install($self) {
  tcssmerger::i()->addstyle(dirname(__file__) . '/paginator3000.css');
  $name = basename(dirname(__file__));
  $about = tplugins::getabout($name);
  $js = tjsmerger::i();
  $js->lock();
  $js->add('default', "/plugins/$name/paginator3000.min.js");
  $js->addtext('default', 'pagenator',
sprintf('var lang = $.extend(true, lang, { pagenator: %s });',
  json_encode(array(
  'next' =>  $about['next'],
  'last' => $about['last'],
  'prior' => $about['prior'],
  'first' => $about['first']
  ))));
  $js->unlock();
  
  
  tthemeparser::i()->parsed = $self->themeparsed;
  ttheme::clearcache();
}

function tpagenator3000Uninstall($self) {
  $name = basename(dirname(__file__));
  $js = tjsmerger::i();
  $js->lock();
  $js->deletefile('default', "/plugins/$name/paginator3000.min.js");
  $js->deletetext('default', 'pagenator');
  $js->unlock();
  
  tthemeparser::i()->unbind($self);
  ttheme::clearcache();
  
  tcssmerger::i()->deletestyle(dirname(__file__) . '/paginator3000.css');
}