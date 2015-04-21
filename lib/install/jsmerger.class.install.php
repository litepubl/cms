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
  $self->add($section, '/js/litepubl/common/litepubl.namespace.min.js')
  $self->add($section, '/js/litepubl/system/ready2.min.js');
  $self->add($section, '/js/litepubl/system/css-loader.min.js');
  $self->add($section, '/js/litepubl/system/json-rpc.min.js');
  $self->add($section, '/js/litepubl/system/load-script.min.js');
  $self->add($section, '/js/litepubl/system/html-comments.min.js');
  $self->add($section, '/js/litepubl/system/escape.min.js');
  $self->add($section, '/js/litepubl/common/widgets.min.js');
  $self->add($section, '/js/litepubl/system/parsetml.min.js');
  $self->add($section, '/js/litepubl/common/templates.min.js');
  $self->add($section, '/js/litepubl/common/filelist.min.js');
  $self->add($section, '/js/litepubl/common/dialog.min.js');
  $self->add($section, '/js/litepubl/common/players.min.js');

tjsmerger_switch($self, tjsmerger_pretty_files(), array());
//tjsmerger_switch($self, tjsmerger_bootstrap_files(), array());
  $self->add($section, "/lib/languages/$language/default.min.js");
  
  $section = 'comments';
  $self->add($section, '/js/litepubl/comments/comments.min.js');
  $self->add($section, '/js/litepubl/comments/confirmcomment.min.js');
  $self->add($section, '/js/litepubl/comments/moderate.min.js');
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
  $self->add($section, '/js/litepubl/admin/admin.min.js');
  $self->add($section, '/js/litepubl/admin/calendar.min.js');
  $self->add($section, "/lib/languages/$language/admin.min.js");
  
  $section = 'adminviews';
  $self->add($section, '/js/jquery/ui/draggable.min.js');
  $self->add($section, '/js/jquery/ui/droppable.min.js');
  $self->add($section, '/js/jquery/ui/resizable.min.js');
  $self->add($section, '/js/jquery/ui/selectable.min.js');
  $self->add($section, '/js/jquery/ui/sortable.min.js');
  $self->add($section, '/js/litepubl/admin/admin.views.min.js');
  
  $section = 'posteditor';
  $self->add($section, '/js/jquery/ui/progressbar.min.js');
  $self->add($section, '/js/swfupload/swfupload.min.js');
  $self->add($section, '/js/plugins/filereader.min.js');
  $self->add($section, '/js/litepubl/admin/uploader.min.js');
  $self->add($section, '/js/litepubl/admin/uploader.html.min.js');
  $self->add($section, '/js/litepubl/admin/uploader.flash.min.js');
  $self->add($section, '/js/litepubl/admin/posteditor.min.js');
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
   '/js/litepubl/pretty/dialog.pretty.min.js',
   '/js/litepubl/pretty/pretty.init.min.js',
   '/js/litepubl/pretty/youtubefix.min.js',
   '/js/litepubl/pretty/player.pretty.min.js',
);
}

function tjsmerger_bootstrap_files() {
return array(
'/js/litepubl/bootstrap/tooltip.init.min.js',
'/js/litepubl/bootstrap/popover.single.min.js',
'/js/litepubl/bootstrap/widgets.bootstrap.min.js',
'/js/litepubl/bootstrap/dialog.bootstrap.min.js',
'/js/litepubl/bootstrap/dialog.simpler.min.js',
'/js/litepubl/bootstrap/player.bootstrap.min.js',
'/js/litepubl/bootstrap/popover.image.min.js',
'/js/litepubl/bootstrap/popover.post.min.js',
'/js/litepubl/bootstrap/youtube.bootstrap.min.js',
'/js/litepubl/bootstrap/theme.init.min.js',
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