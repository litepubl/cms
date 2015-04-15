<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TRSSPrevNextInstall($self) {
  $rss = trss::i();
  $rss->beforepost = $self->beforepost;
  
  $urlmap = turlmap::i();
  $urlmap->clearcache();
}

function TRSSPrevNextUninstall($self) {
  $rss = trss::i();
  $rss->unbind($self);
  
  $urlmap = turlmap::i();
  $urlmap->clearcache();
}