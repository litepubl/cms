<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\plugins\metatags;

use litepubl\view\AutoVars;
use litepubl\view\Base;
use litepubl\view\MainView;
use litepubl\view\Parser;

function PluginInstall($self)
{
    $vars = AutoVars::i();
    $vars->items['metatags'] = get_class($self);
    $vars->save();

    $t = MainView::i();
    $t->heads = strtr(
        $t->heads, [
        '$template.keywords' => '$metatags.keywords',
        '$template.description' => '$metatags.description',
        ]
    );
    $t->save();

    Parser::i()->parsed = $self->themeParsed;
    Base::clearCache();
}

function PluginUninstall($self)
{
    $t = MainView::i();
    $t->heads = strtr(
        $t->heads, [
        '$metatags.keywords' => '$template.keywords',
        '$metatags.description' => '$template.description'
        ]
    );
    $t->save();

    Parser::i()->unbind($self);
    Base::clearCache();

    $vars = AutoVars::i();
    unset($vars->items['metatags']);
    $vars->save();
}
