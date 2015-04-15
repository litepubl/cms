<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tajaxposteditorInstall($self) {
  litepublisher::$urlmap->addget('/admin/ajaxposteditor.htm', get_class($self));
}

function tajaxposteditorUninstall($self) {
  turlmap::unsub($self);
}