<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function bootstrap_themeInstall($self) {
  $js = tjsmerger::i();
  $js->  externalfunc(get_class($js), '_switch', array(
  $js->externalfunc(get_class($js), '_bootstrap_files', false),
  $js->externalfunc(get_class($js), '_pretty_files', false)
  ));
  
  $css = tcssmerger::i();
  $css->lock();
  tjsmerger_switch ($css,
  array(),
  $css->externalfunc(get_class($css), '_pretty_files', false)
  );
  
  tjsmerger_switch ($css,
  array(),
  $css->externalfunc(get_class($css), '_deprecated_files', false)
  );
  
  $css->unlock();
  
  ttheme::clearcache();
}

function bootstrap_themeUninstall($self) {
  $js = tjsmerger::i();
  $js->  externalfunc(get_class($js), '_switch', array(
  $js->externalfunc(get_class($js), '_pretty_files', false),
  $js->externalfunc(get_class($js), '_bootstrap_files', false),
  ));
  
  $css = tcssmerger::i();
  $css->lock();
  tjsmerger_switch ($css,
  $css->externalfunc(get_class($css), '_pretty_files', false)
  array(),
  );
  
  tjsmerger_switch ($css,
  $css->externalfunc(get_class($css), '_deprecated_files', false)
  array(),
  );
  
  $css->unlock();
  
  ttheme::clearcache();
}