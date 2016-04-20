<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function tsinglecatInstall($self) {
    if (!dbversion) die('Required database version');
    tthemeparser::i()->parsed = $self->themeparsed;
    ttheme::clearcache();
}

function tsinglecatUninstall($self) {
    tthemeparser::i()->unbind($self);
    ttheme::clearcache();
}