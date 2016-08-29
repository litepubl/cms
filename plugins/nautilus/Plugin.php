<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace litepubl\plugins\nautilus;

use litepubl\view\Css;
use litepubl\view\Js;

class Plugin extends \litepubl\core\Plugin
{

    public function install()
    {
        $plugindir = basename(dirname(__file__));
        Js::i()->add('default', "plugins/$plugindir/resource/nautilus.min.js");
        Css::i()->add('default', "plugins/$plugindir/resource/nautilus.min.css");
    }

    public function uninstall()
    {
        $plugindir = basename(dirname(__file__));
        Js::i()->deletefile('default', "plugins/$plugindir/resource/nautilus.min.js");
        Css::i()->deletefile('default', "plugins/$plugindir/resource/nautilus.min.css");
    }
}
