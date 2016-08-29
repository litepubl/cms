<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace litepubl\plugins\smallplugs_literu;

use litepubl\core\Plugins;
use litepubl\pages\Menus;
use litepubl\utils\Backuper;
use litepubl\view\AutoVars;
use litepubl\view\Parser;

function literuInstall($self)
{
    Menus::i()->oncontent = $self->onMenuContent;
    Backuper::i()->onuploaded = $self->onuploaded;
    Plugins::i()->add('smallplugs_enscroll');
    $plugindir = basename(dirname(__file__));
    Parser::i()->addTags("plugins/$plugindir/resource/theme.txt", false);
    AutoVars::i()->add('literu', get_class($self));
}

function literuUninstall($self)
{
    Menus::i()->unbind($self);
    Backuper::i()->unbind($self);
    Plugins::i()->delete('smallplugs_enscroll');
    $plugindir = basename(dirname(__file__));
    Parser::i()->removetags("plugins/$plugindir/resource/theme.txt", false);
    AutoVars::i()->delete('literu');
}
