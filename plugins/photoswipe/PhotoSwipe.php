<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\plugins\photoswipe;
use litepubl\view\Js;
use litepubl\view\Css;

class PhotoSwipe extends \litepubl\core\Plugin
 {

    public function install() {
        $plugindir = basename(dirname(__file__));
        $lang =  $this->getApp()->options->language;

        $js = Js::i();
        $js->lock();
        //remove popimage
        $js->deletefile('default', '/js/litepubl/bootstrap/popover.image.min.js');
        $js->deletefile('default', '/js/litepubl/bootstrap/popover.image.init.min.js');

        $js->add('default', "plugins/$plugindir/resource/photoswipe.min.js");
        $js->add('default', "plugins/$plugindir/resource/photoswipe-ui-default.min.js");
        $js->add('default', "plugins/$plugindir/resource/photoswipe.plugin.tml.min.js");
        $js->add('default', "plugins/$plugindir/resource/$lang.photoswipe.plugin.min.js");
        $js->add('default', "plugins/$plugindir/resource/photoswipe.plugin.min.js");
        $js->unlock();

        $css = Css::i();
        $css->lock();
        $css->add('default', "plugins/$plugindir/resource/photoswipe.min.css");
        $css->add('default', "plugins/$plugindir/resource/default-skin/default-skin.inline.min.css");
        $css->unlock();
    }

    public function uninstall() {
        $plugindir = basename(dirname(__file__));
        $lang =  $this->getApp()->options->language;

        $js = Js::i();
        $js->lock();
        $js->deletefile('default', "plugins/$plugindir/resource/photoswipe.min.js");
        $js->deletefile('default', "plugins/$plugindir/resource/photoswipe-ui-default.min.js");
        $js->deletefile('default', "plugins/$plugindir/resource/photoswipe.plugin.tml.min.js");
        $js->deletefile('default', "plugins/$plugindir/resource/$lang.photoswipe.plugin.min.js");
        $js->deletefile('default', "plugins/$plugindir/resource/photoswipe.plugin.min.js");
        $js->unlock();

        $css = Css::i();
        $css->lock();
        $css->deletefile('default', "plugins/$plugindir/resource/photoswipe.min.css");
        $css->deletefile('default', "plugins/$plugindir/resource/default-skin/default-skin.inline.min.css");
        $css->unlock();
    }

} //class