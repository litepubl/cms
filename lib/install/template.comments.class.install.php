<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function ttemplatecommentsInstall($self) {
  tlocal::usefile('install');
  $lang = tlocal::i('beforecommentsform');
$login = '<a class="log-in" href="$site.url/admin/login/{$site.q}backurl=">' . $lang->log_in . '</a>';
  
  $self->data['logged'] = sprintf($lang->logged,
  '<?php echo litepublisher::$site->getuserlink(); ?>',
' <a class="logout" href="$site.url/admin/logout/{$site.q}backurl=">' . $lang->logout . '</a> ');
  
  $self->data['adminpanel'] = sprintf($lang->adminpanel,
  '<a class="admin-panel" href="$site.url/admin/comments/">' . $lang->controlpanel . '</a>');
  
  $self->data['reqlogin'] = sprintf($lang->reqlogin,$login);
  
  $self->data['guest'] = sprintf($lang->guest, $login);
  
  $self->data['regaccount'] = sprintf($lang->regaccount,
'<a class="registration" href="$site.url/admin/reguser/{$site.q}backurl=">' . $lang->signup . '</a>');
  
  $self->data['comuser'] = sprintf($lang->comuser, $login);
  
  $self->data['loadhold'] = sprintf('<h4>%s</h4>', sprintf($lang->loadhold,
  '<a class="loadhold " href="$site.url/admin/comments/hold/">' . $lang->loadhold . '</a>'));
  
  $self->save();
}