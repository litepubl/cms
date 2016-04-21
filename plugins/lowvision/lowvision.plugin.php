<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Js;
use litepubl\view\Css;
use litepubl\core\Plugins;

class lowvision extends \litepubl\core\Plugin
 {

    public static function i() {
        return getinstance(__class__);
    }

    public function install() {
        $plugindir = basename(dirname(__file__));
        Js::i()->add('default', "plugins/$plugindir/resource/lowvision.min.js");
        Css::i()->add('default', "plugins/$plugindir/resource/lowvision.min.css");

        $about = Plugins::getabout(basename(dirname(__file__)));
        $this->data['idwidget'] = tcustomwidget::i()->add(1, $about['title'], file_get_contents(dirname(__file__) . '/resource/widget.html') , 'widget');
        $this->save();
    }

    public function uninstall() {
        tcustomwidget::i()->delete($this->idwidget);

        $plugindir = basename(dirname(__file__));
        Js::i()->deletefile('default', "plugins/$plugindir/resource/lowvision.min.js");
        Css::i()->deletefile('default', "plugins/$plugindir/resource/lowvision.min.css");
    }

}