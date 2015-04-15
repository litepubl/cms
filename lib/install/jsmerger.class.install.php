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
  $self->add($section, '/js/prettyphoto/js/jquery.prettyPhoto.js');
  $self->add($section, '/js/plugins/class-extend.min.js');
  $self->add($section, '/js/plugins/jquery.cookie.min.js');
  $self->add($section, '/js/plugins/tojson.min.js');
  $self->add($section, '/js/litepublisher/litepublisher.utils.min.js');
  $self->add($section, '/js/litepublisher/ready2.min.js');
  $self->add($section, '/js/litepublisher/css-loader.min.js');
  $self->add($section, '/js/litepublisher/json-rpc.min.js');
  $self->add($section, '/js/litepublisher/widgets.min.js');
  $self->add($section, '/js/litepublisher/widgets.bootstrap.min.js');
  $self->add($section, '/js/litepublisher/simpletml.min.js');
  $self->add($section, '/js/litepublisher/templates.min.js');
  $self->add($section, '/js/litepublisher/filelist.min.js');
  $self->add($section, '/js/litepublisher/players.min.js');
  $self->add($section, '/js/litepublisher/dialog.min.js');
  $self->add($section, '/js/litepublisher/dialog.pretty.min.js');
  $self->add($section, '/js/litepublisher/dialog.bootstrap.min.js');
  $self->add($section, '/js/litepublisher/pretty.init.min.js');
  $self->add($section, '/js/litepublisher/youtubefix.min.js');
  $self->add($section, "/lib/languages/$language/default.min.js");
  
  $section = 'comments';
  $self->add($section, '/js/litepublisher/comments.min.js');
  $self->add($section, '/js/litepublisher/confirmcomment.min.js');
  $self->add($section, '/js/litepublisher/moderate.min.js');
  $self->add($section, "/lib/languages/$language/comments.min.js");
  
  $section = 'admin';
  $self->add($section, '/js/jquery/ui/core.min.js');
  $self->add($section, '/js/jquery/ui/widget.min.js');
  $self->add($section, '/js/jquery/ui/mouse.min.js');
  $self->add($section, '/js/jquery/ui/position.min.js');
  $self->add($section, '/js/jquery/ui/effect.min.js');
  $self->add($section, '/js/jquery/ui/tabs.min.js');
  $self->add($section, '/js/litepublisher/admin.min.js');
  $self->add($section, '/js/litepublisher/calendar.min.js');
  $self->add($section, "/lib/languages/$language/admin.min.js");
  
  $section = 'adminviews';
  $self->add($section, '/js/jquery/ui/draggable.min.js');
  $self->add($section, '/js/jquery/ui/droppable.min.js');
  $self->add($section, '/js/jquery/ui/resizable.min.js');
  $self->add($section, '/js/jquery/ui/selectable.min.js');
  $self->add($section, '/js/jquery/ui/sortable.min.js');
  $self->add($section, '/js/litepublisher/admin.views.min.js');
  
  $section = 'posteditor';
  $self->add($section, '/js/swfupload/swfupload.min.js');
  $self->add($section, '/js/plugins/filereader.min.js');
  $self->add($section, '/js/litepublisher/uploader.min.js');
  $self->add($section, '/js/litepublisher/uploader.html.min.js');
  $self->add($section, '/js/litepublisher/uploader.flash.min.js');
  $self->add($section, '/js/jquery/ui/progressbar.min.js');
  $self->add($section, '/js/litepublisher/posteditor.min.js');
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