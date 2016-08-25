<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\plugins\photoswipe;

use litepubl\view\Css;
use litepubl\view\Js;

class PhotoSwipe extends \litepubl\core\Plugin
{

    public function install()
    {
        $this->add('photoswipe');
    }

    public function add(string $section= 'default')
    {
        $plugindir = basename(dirname(__file__));
        $lang = $this->getApp()->options->language;

        $js = Js::i();
        $js->lock();
        $js->add($section, "plugins/$plugindir/resource/photoswipe.min.js");
        $js->add($section, "plugins/$plugindir/resource/photoswipe-ui-default.min.js");
        $js->add($section, "plugins/$plugindir/resource/photoswipe.plugin.tml.min.js");
        $js->add($section, "plugins/$plugindir/resource/$lang.photoswipe.plugin.min.js");
        $js->add('default', "plugins/$plugindir/resource/photoswipe.plugin.min.js");
        $js->unlock();

        $css = Css::i();
        $css->lock();
        $css->add($section, "plugins/$plugindir/resource/photoswipe.min.css");
        $css->add($section, "plugins/$plugindir/resource/default-skin/default-skin.inline.min.css");
        $css->unlock();
    }

    public function uninstall()
    {
        $this->delete('photoswipe');
    }

    public function delete(string $section = 'default')
    {
        $plugindir = basename(dirname(__file__));
        $lang = $this->getApp()->options->language;

        $js = Js::i();
        $js->lock();
        $js->deletefile($section, "plugins/$plugindir/resource/photoswipe.min.js");
        $js->deletefile($section, "plugins/$plugindir/resource/photoswipe-ui-default.min.js");
        $js->deletefile($section, "plugins/$plugindir/resource/photoswipe.plugin.tml.min.js");
        $js->deletefile($section, "plugins/$plugindir/resource/$lang.photoswipe.plugin.min.js");
        $js->deletefile('default', "plugins/$plugindir/resource/photoswipe.plugin.min.js");

        if ($section == 'photoswipe') {
            $js->deleteSection($section);
        }
        $js->unlock();

        $css = Css::i();
        $css->lock();
        if ($section == 'photoswipe') {
            $css->deleteSection($section);
        } else {
                $css->deletefile($section, "plugins/$plugindir/resource/photoswipe.min.css");
                $css->deletefile($section, "plugins/$plugindir/resource/default-skin/default-skin.inline.min.css");
        }
        $css->unlock();
    }
}
