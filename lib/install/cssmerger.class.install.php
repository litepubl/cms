<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcssmergerInstall($self) {
  $self->lock();
  $self->items = array();
  $section = 'default';
  $self->add($section, '/js/prettyphoto/css/prettyPhoto.css');
  $self->add($section, '/js/litepublisher/css/prettyphoto.dialog.min.css');
  $self->add($section, '/js/litepublisher/css/filelist.min.css');
  $self->add($section, '/js/litepublisher/css/table.min.css');
  $self->add($section, '/js/litepublisher/css/button.min.css');
  $self->add($section, '/js/litepublisher/css/form.inline.min.css');
  //$self->add($section, '/js/litepublisher/css/hover.css');
$self->addtext($section, 'hidden', '.hidden{display:none}');
  
  $section = 'admin';
  $self->add($section, '/js/jquery/ui/redmond/jquery-ui.min.css');
  $self->add($section, '/js/litepublisher/css/fileman.min.css');
  $self->add($section, '/js/litepublisher/css/calendar.css');
  $self->add($section, '/js/litepublisher/css/admin.views.min.css');
  $self->unlock();
  
  // add in comment because by default tthemeparser::i()->stylebefore  is true
  $template = ttemplate::i();
  $template->addtohead('<!--<link type="text/css" href="$site.files$template.cssmerger_default" rel="stylesheet" />-->');
  
  $updater = tupdater::i();
  $updater->onupdated = $self->save;
}

function tcssmergerUninstall($self) {
  $updater = tupdater::i();
  $updater->unbind($self);
}