<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function texcerptslideInstall($self) {
  $widgets = twidgets::i();
  $widgets->deleted = $self->widgetdeleted;
  
  $self->tags->deleted = $self->tagdeleted;
  
tcssmerger::i()->addtext('default', 'excerptslide', '.excerptslide p { display:none;}');
  tjsmerger::i()->addtext('default', 'excerptslide',
  '$(document).ready(function() {
    $(".excerptslide_link").click(function() {
      $(this).parent().children("p").slideToggle();
      return false;
    });
  });
  ');
}

function texcerptslideUninstall($self) {
  $self->tags->unbind($self);
  
  tjsmerger::i()->deletetext('default', 'excerptslide');
  tcssmerger::i()->deletetext('default', 'excerptslide');
}