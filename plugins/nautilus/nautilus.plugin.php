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

use litepubl\view\Css;
use litepubl\view\Js;

class nautilus_font extends \litepubl\core\Plugin
{

    public static function i()
    {
        return static ::iGet(__class__);
    }

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

