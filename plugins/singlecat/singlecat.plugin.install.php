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

function tsinglecatInstall($self) {
    if (!dbversion) die('Required database version');
    Parser::i()->parsed = $self->themeparsed;
    Base::clearCache();
}

function tsinglecatUninstall($self) {
    Parser::i()->unbind($self);
    Base::clearCache();
}