<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\plugins\clearcache;

use litepubl\view\Parser;

function ClearCacheInstall($self)
{
    $self->getApp()->router->beforerequest = $self->clearCache;
    $parser = Parser::i();
    $parser->parsed = $self->parsed;
}

function ClearCacheUninstall($self)
{
    $self->getApp()->router->unbind($self);
    $parser = Parser::i();
    $parser->unbind($self);
}
