<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 */

namespace litepubl\plugins\singlecat;

use litepubl\view\AutoVars;
use litepubl\view\Base;
use litepubl\view\Parser;

function PluginInstall($self)
{
    $vars = AutoVars::i();
    $vars->items['singlecat'] = get_class($self);
    $vars->save();

    Parser::i()->parsed = $self->themeparsed;
    Base::clearCache();
}

function PluginUninstall($self)
{
    Parser::i()->unbind($self);
    Base::clearCache();

    $vars = AutoVars::i();
    unset($vars->items['singlecat']);
    $vars->save();
}
