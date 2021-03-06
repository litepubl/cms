<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\extrasidebars;

use litepubl\view\Base;
use litepubl\view\Parser;

function ExtraSidebarsInstall($self)
{
    $parser = Parser::i();
    $parser->lock();
    $parser->onfix = $self->fix;
    $parser->parsed = $self->themeParsed;
    $parser->unlock();

    Base::clearCache();
}

function ExtraSidebarsUninstall($self)
{
    Parser::i()->unbind($self);
    Base::clearCache();
}
