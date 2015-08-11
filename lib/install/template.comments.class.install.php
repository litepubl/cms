<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function ttemplatecommentsInstall($self) {
  tlocal::usefile('install');
  $lang = tlocal::i('beforecommentsform');
$login = '<a class="log-in" href="$site.url/admin/login/{$site.q}backurl=">' . $lang->log_in . '</a>';
  
 
  
  $self->save();
}