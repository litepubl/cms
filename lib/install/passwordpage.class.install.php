<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpasswordpageInstall($self) {
  litepublisher::$urlmap->delete('/check-password.php');
  tlocal::usefile('install');
  $lang = tlocal::i('passwordpage');
  
  $form = '<h3>$lang.formtitle</h3>
  <form name="form" action="" method="post" >
  <p><input type="password" name="password" id="password-password" value="" size="22" />
  <label for="password-password"><strong>$lang.password</strong></label></p>
  
  <p><input type="checkbox" name="remember" id="checkbox-remember" $remember />
  <label for="checkbox-remember"><strong>$lang.remember</strong></label></p>
  
  <p>
  <input type="hidden" name="antispam" id="hidden-antispam" value="$antispam" />
  <input type="submit" name="submitbutton" id="submitbutton" value="$lang.send" />
  </p>
  </form>';
  
  $self->data['form'] =ttheme::i()->parse($form);
  $self->data['title'] = $lang->reqpassword;
  $self->data['invalidpassword'] = $lang->invalidpassword;
  $self->save();
  
  trobotstxt ::i()->AddDisallow('/check-password.php');
  
  litepublisher::$urlmap->addget('/check-password.php', get_class($self));
}

function tpasswordpageUninstall($self) {
  litepublisher::$urlmap->delete('/check-password.php');
}