<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Base;
use litepubl\view\Parser;

function tmetatagsInstall($self) {
     $self->getApp()->classes->classes['metatags'] = get_class($self);
     $self->getApp()->classes->save();

    $t = ttemplate::i();
    $t->heads = strtr($t->heads, array(
        '$template.keywords' => '$metatags.keywords',
        '$template.description' => '$metatags.description',
    ));
    $t->save();

    Parser::i()->parsed = $self->themeparsed;
    Base::clearCache();
}

function tmetatagsUninstall($self) {
    $t = ttemplate::i();
    $t->heads = strtr($t->heads, array(
        '$metatags.keywords' => '$template.keywords',
        '$metatags.description' => '$template.description'
    ));
    $t->save();

    Parser::i()->unbind($self);
    Base::clearCache();

    unset( $self->getApp()->classes->classes['metatags']);
     $self->getApp()->classes->save();
}