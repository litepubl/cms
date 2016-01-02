<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tpostcatwidgetInstall($self) {
  $widgets = twidgets::i();
  $widgets->deleted = $self->widgetdeleted;
  
  tcategories::i()->deleted = $self->tagdeleted;
}

function tpostcatwidgetUninstall($self) {
  tcategories::i()->unbind($self);
  $widgets = twidgets::i();
  $widgets->unbind($self);
}