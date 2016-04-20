<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function tmetatagsInstall($self) {
     $self->getApp()->classes->classes['metatags'] = get_class($self);
     $self->getApp()->classes->save();

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

    unset( $self->getApp()->classes->classes['metatags']);
     $self->getApp()->classes->save();
}