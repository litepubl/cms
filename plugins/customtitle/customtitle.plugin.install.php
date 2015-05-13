<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tcustomtitleInstall($self) {
  $template = ttemplate::i();
  $template->ontitle = $self->ontitle;
}

function tcustomtitleUninstall($self) {
  $template = ttemplate::i();
  $template->unbind($self);
}