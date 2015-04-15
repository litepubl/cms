<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tprofileInstall($self) {
  litepublisher::$urlmap->add($self->url, get_class($self), null);
  
  $sitemap = tsitemap::i();
  $sitemap->add($self->url, 7);
  
  $template = ttemplate::i();
  $template->addtohead('	<link rel="author profile" title="Profile" href="$site.url/profile.htm" />');
}

function tprofileUninstall($self) {
  turlmap::unsub($self);
  
  $sitemap = tsitemap::i();
  $sitemap->delete('/profile.htm');
  
  $template = ttemplate::i();
  $template->deletefromhead('	<link rel="author profile" title="Profile" href="$site.url/profile.htm" />');
}