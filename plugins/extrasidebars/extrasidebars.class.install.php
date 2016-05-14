<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

use litepubl\view\Base;
use litepubl\view\Parser;

function textrasidebarsInstall($self)
{
    $parser = Parser::i();
    $parser->lock();
    $parser->onfix = $self->fix;
    $parser->parsed = $self->themeparsed;
    $parser->unlock();

    Base::clearCache();
}

function textrasidebarsUninstall($self)
{
    Parser::i()->unbind($self);
    Base::clearCache();
}

