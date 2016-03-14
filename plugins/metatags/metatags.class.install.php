<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function tmetatagsInstall($self) {
  litepublisher::$classes->classes['metatags'] = get_class($self);
  litepublisher::$classes->save();

  $t = ttemplate::i();
  $t->heads = strtr($t->heads, array(
    '$template.keywords' => '$metatags.keywords',
    '$template.description' => '$metatags.description',
  ));
  $t->save();

  tthemeparser::i()->parsed = $self->themeparsed;
  ttheme::clearcache();
}

function tmetatagsUninstall($self) {
  $t = ttemplate::i();
  $t->heads = strtr($t->heads, array(
    '$metatags.keywords' => '$template.keywords',
    '$metatags.description' => '$template.description'
  ));
  $t->save();

  tthemeparser::i()->unbind($self);
  ttheme::clearcache();

  unset(litepublisher::$classes->classes['metatags']);
  litepublisher::$classes->save();
}