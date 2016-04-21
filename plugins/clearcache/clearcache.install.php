<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Parser;

function tclearcacheInstall($self) {
     $self->getApp()->router->beforerequest = $self->clearcache;
    $parser = Parser::i();
    $parser->parsed = $self->themeparsed;
}

function tclearcacheUninstall($self) {
     $self->getApp()->router->unbind($self);
    $parser = Parser::i();
    $parser->unbind($self);
}