<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;
use litepubl\view\Lang;

function PasswordInstall($self) {
    litepubl::$urlmap->delete('/check-password.php');
    Lang::usefile('install');
    $lang = Lang::i('passwordpage');

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

    $self->data['form'] = ttheme::i()->parse($form);
    $self->data['title'] = $lang->reqpassword;
    $self->data['invalidpassword'] = $lang->invalidpassword;
    $self->save();

    RobotsTxt::i()->AddDisallow($self->url);
    litepubl::$urlmap->addget($self->url, get_class($self));
}

function PasswordUninstall($self) {
    litepubl::$urlmap->umbind($self);
}