<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tjsmergerInstall($self) {
  $dir = litepublisher::$paths->files . 'js';
  @mkdir($dir, 0777);
  @chmod($dir, 0777);
  $file = $dir . DIRECTORY_SEPARATOR . 'index.htm';
  file_put_contents($file, ' ');
  @chmod($file, 0666);
  
  $language = litepublisher::$options->language;
  $self->lock();
  $self->items = array();
  $section = 'default';
  $self->add($section, '/js/jquery/jquery-$site.jquery_version.min.js');
  $self->add($section, '/js/plugins/class-extend.min.js');
  $self->add($section, '/js/plugins/jquery.cookie.min.js');
  $self->add($section, '/js/plugins/tojson.min.js');
  $self->add($section, '/js/litepublisher/litepublisher.utils.min.js');
  $self->add($section, '/js/litepublisher/ready2.min.js');
  $self->add($section, '/js/litepublisher/css-loader.min.js');
  $self->add($section, '/js/litepublisher/json-rpc.min.js');
  $self->add($section, '/js/litepublisher/widgets.min.js');
  $self->add($section, '/js/litepublisher/simpletml.min.js');
  $self->add($section, '/js/litepublisher/templates.min.js');
  $self->add($section, '/js/litepublisher/filelist.min.js');
  $self->add($section, '/js/litepublisher/dialog.min.js');
  $self->add($section, '/js/litepublisher/players.min.js');
tjsmerger_switch($self, tjsmerger_pretty_files(), array());
//tjsmerger_switch($self, tjsmerger_bootstrap_files(), array());
  $self->add($section, "/lib/languages/$language/default.min.js");
  
  $section = 'comments';
  $self->add($section, '/js/litepublisher/comments.min.js');
  $self->add($section, '/js/litepublisher/confirmcomment.min.js');
  $self->add($section, '/js/litepublisher/moderate.min.js');
  $self->add($section, "/lib/languages/$language/comments.min.js");

  $section = 'media';
  $self->add($section, '/js/mediaelement/mediaelement-and-player.min.js');
  if ($language != 'en') $self->add($section, "/lib/languages/$language/mediaplayer.min.js");
  
  $section = 'admin';
  $self->add($section, '/js/jquery/ui/core.min.js');
  $self->add($section, '/js/jquery/ui/widget.min.js');
  $self->add($section, '/js/jquery/ui/mouse.min.js');
  $self->add($section, '/js/jquery/ui/position.min.js');
  $self->add($section, '/js/jquery/ui/effect.min.js');
  $self->add($section, '/js/jquery/ui/tabs.min.js');
  $self->add($section, '/js/admin/admin.min.js');
  $self->add($section, '/js/admin/calendar.min.js');
  $self->add($section, "/lib/languages/$language/admin.min.js");
  
  $section = 'adminviews';
  $self->add($section, '/js/jquery/ui/draggable.min.js');
  $self->add($section, '/js/jquery/ui/droppable.min.js');
  $self->add($section, '/js/jquery/ui/resizable.min.js');
  $self->add($section, '/js/jquery/ui/selectable.min.js');
  $self->add($section, '/js/jquery/ui/sortable.min.js');
  $self->add($section, '/js/admin/admin.views.min.js');
  
  $section = 'posteditor';
  $self->add($section, '/js/jquery/ui/progressbar.min.js');
  $self->add($section, '/js/swfupload/swfupload.min.js');
  $self->add($section, '/js/plugins/filereader.min.js');
  $self->add($section, '/js/admin/uploader.min.js');
  $self->add($section, '/js/admin/uploader.html.min.js');
  $self->add($section, '/js/admin/uploader.flash.min.js');
  $self->add($section, '/js/admin/posteditor.min.js');
  $self->add($section, '/js/litepublisher/fileman.min.js');
  $self->add($section, '/js/litepublisher/fileman.templates.min.js');
  $self->add($section, "/lib/languages/$language/posteditor.min.js");
  
  $self->unlock();
  
  $template = ttemplate::i();
  $template->addtohead(sprintf($template->js, '$site.files$template.jsmerger_default'));
  
  $updater = tupdater::i();
  $updater->onupdated = $self->onupdated;
}

function tjsmergerUninstall($self) {
  tupdater::i()->unbind($self);
}

function tjsmerger_pretty_files() {
return array(
   '/js/prettyphoto/js/jquery.prettyPhoto.js',
   '/js/prettyphoto/litepubl/dialog.pretty.min.js',
   '/js/prettyphoto/litepubl/pretty.init.min.js',
   '/js/prettyphoto/litepubl/youtubefix.min.js',
   '/js/prettyphoto/litepubl/player.pretty.min.js',
);
}

function tjsmerger_bootstrap_files() {
return array(
'/js/bootstrap/widgets.bootstrap.min.js',
'/js/bootstrap/dialog.bootstrap.min.js',
'/js/bootstrap/dialog.simpler.min.js',
'/js/bootstrap/player.bootstrap.min.js',
'/js/bootstrap/popimage.min.js',
'/js/bootstrap/single-popover.min.js',
'/js/bootstrap/youtube.bootstrap.min.js',
);
}

function tjsmerger_switch($self, $add, $delete) {

$self->lock();
foreach ($delete as $filename) {
$self->deletefile('default', $filename);
}

foreach ($add as $filename) {
$self->add('default', $filename);
}

$self->unlock();
}