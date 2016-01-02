<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function trssfilelistInstall($self) {
  $rss = trss::i();
  $rss->beforepost = $self->beforepost;
  
  litepublisher::$urlmap->clearcache();
}

function trssfilelistUninstall($self) {
  $rss = trss::i();
  $rss->unbind($self);
  
  litepublisher::$urlmap->clearcache();
}