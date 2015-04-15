<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tkeywordspluginInstall($self) {
  @mkdir(litepublisher::$paths->data . 'keywords', 0777);
  @chmod(litepublisher::$paths->data . 'keywords', 0777);
  
  $item = litepublisher::$classes->items[get_class($self)];
  litepublisher::$classes->add('tkeywordswidget','keywords.widget.php', $item[1]);
  
  $widget = tkeywordswidget::i();
  $widgets = twidgets::i();
  $widgets->lock();
  $id = $widgets->add($widget);
  $sidebars = tsidebars::i();
  $sidebars->insert($id, false, 1, -1);
  $widgets->unlock();
  
  $urlmap = turlmap::i();
  $urlmap->lock();
  $urlmap->afterrequest = $self->parseref;
  $urlmap->deleted = $self->urldeleted;
  $urlmap->unlock();
}

function tkeywordspluginUninstall($self) {
  turlmap::unsub($self);
  $widgets = twidgets::i();
  $widgets->deleteclass('tkeywordswidget');
  litepublisher::$classes->delete('tkeywordswidget');
  //TFiler::DeleteFiles(litepublisher::$paths->data . 'keywords' . DIRECTORY_SEPARATOR  , true);
}