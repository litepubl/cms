<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function tmetatagsInstall($self) {
    litepubl::$classes->classes['metatags'] = get_class($self);
    litepubl::$classes->save();

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

    unset(litepubl::$classes->classes['metatags']);
    litepubl::$classes->save();
}