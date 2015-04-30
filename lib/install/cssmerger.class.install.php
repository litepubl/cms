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
$items = tcssmerger_pretty_files($self);
foreach ($list as $filename) {
$self->add($section, $filename);
}

$items = tcssmerger_deprecated_files($self);
foreach ($list as $filename) {
$self->add($section, $filename);
}

  $self->add($section, '/js/litepubl/common/css/common.min.css');
  $self->add($section, '/js/litepubl/common/css/filelist.min.css');
  $self->add($section, '/js/litepubl/common/css/form-inline.min.css');

  $section = 'admin';
  $self->add($section, '/js/jquery/ui/redmond/jquery-ui.min.css');
  $self->add($section, '/js/admin/css/fileman.min.css');
  $self->add($section, '/js/admin/css/calendar.css');
  $self->add($section, '/js/litepublisher/css/admin.views.min.css');
  $self->unlock();
  
  // add in comment because by default tthemeparser::i()->stylebefore  is true
  $template = ttemplate::i();
  $template->addtohead('<!--<link type="text/css" href="$site.files$template.cssmerger_default" rel="stylesheet" />-->');
  
tupdater::i()->onupdated = $self->save;
}

function tcssmergerUninstall($self) {
tupdater::i()->unbind($self);
}

function tcssmerger_pretty_files($self) {
  return array(
'/js/prettyphoto/css/prettyPhoto.css',
'/js/litepubl/pretty/dialog.pretty.min.css',
);
}

function tcssmerger_deprecated_files($self) {
  return array(
	'/js/litepubl/deprecated/css/align.min.css',
	'/js/litepubl/deprecated/css/button.min.css',
'/js/litepubl/deprecated/css/table.min.css',
);
}
