<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function textrasidebarsInstall($self) {
    $parser = tthemeparser::i();
    $parser->lock();
    $parser->onfix = $self->fix;
    $parser->parsed = $self->themeparsed;
    $parser->unlock();

    ttheme::clearcache();
}

function textrasidebarsUninstall($self) {
    tthemeparser::i()->unbind($self);
    ttheme::clearcache();
}