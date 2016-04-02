<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class photoswipe extends tplugin {

  public static function i() {
    return getinstance(__class__);
  }

  public function install() {
    $plugindir = basename(dirname(__file__));
    $lang = litepubl::$options->language;

    $js = tjsmerger::i();
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

    $css = tcssmerger::i();
    $css->lock();
    $css->add('default', "plugins/$plugindir/resource/photoswipe.min.css");
    $css->add('default', "plugins/$plugindir/resource/default-skin/default-skin.inline.min.css");
    $css->unlock();
  }

  public function uninstall() {
    $plugindir = basename(dirname(__file__));
    $lang = litepubl::$options->language;

    $js = tjsmerger::i();
    $js->lock();
    $js->deletefile('default', "plugins/$plugindir/resource/photoswipe.min.js");
    $js->deletefile('default', "plugins/$plugindir/resource/photoswipe-ui-default.min.js");
    $js->deletefile('default', "plugins/$plugindir/resource/photoswipe.plugin.tml.min.js");
    $js->deletefile('default', "plugins/$plugindir/resource/$lang.photoswipe.plugin.min.js");
    $js->deletefile('default', "plugins/$plugindir/resource/photoswipe.plugin.min.js");
    $js->unlock();

    $css = tcssmerger::i();
    $css->lock();
    $css->deletefile('default', "plugins/$plugindir/resource/photoswipe.min.css");
    $css->deletefile('default', "plugins/$plugindir/resource/default-skin/default-skin.inline.min.css");
    $css->unlock();
  }

} //class