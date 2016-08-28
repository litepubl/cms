<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\plugins\lowvision;

use litepubl\core\Plugins;
use litepubl\view\Css;
use litepubl\view\Js;
use litepubl\widget\Custom;

class LowVision extends \litepubl\core\Plugin
{

    public function install()
    {
        $plugindir = basename(dirname(__file__));
        Js::i()->add('default', "plugins/$plugindir/resource/lowvision.min.js");
        Css::i()->add('default', "plugins/$plugindir/resource/lowvision.min.css");

        $about = Plugins::getAbout(basename(dirname(__file__)));
        $this->data['idwidget'] = Custom::i()->add(1, $about['title'], file_get_contents(dirname(__file__) . '/resource/widget.html'), 'widget');
        $this->save();
    }

    public function uninstall()
    {
        Custom::i()->delete($this->idwidget);

        $plugindir = basename(dirname(__file__));
        Js::i()->deletefile('default', "plugins/$plugindir/resource/lowvision.min.js");
        Css::i()->deletefile('default', "plugins/$plugindir/resource/lowvision.min.css");
    }
}
