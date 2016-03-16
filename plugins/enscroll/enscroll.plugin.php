<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class enscroll extends tplugin {

  public static function i() {
    return getinstance(__class__);
  }

  public function install() {
    $plugindir = basename(dirname(__file__));
    $js = tjsmerger::i();
    $js->lock();
    $js->add('default', "plugins/$plugindir/resource/enscroll-0.6.1.min.js");
    $js->add('default', "plugins/$plugindir/resource/init.js");
    $js->unlock();

    $css = tcssmerger::i();
    $css->lock();
    $css->add('default', "plugins/$plugindir/resource/enscroll.min.css");
    $css->unlock();
  }

  public function uninstall() {
    $plugindir = basename(dirname(__file__));
    $js = tjsmerger::i();
    $js->lock();
    $js->deletefile('default', "plugins/$plugindir/resource/enscroll-0.6.1.min.js");
    $js->deletefile('default', "plugins/$plugindir/resource/init.js");
    $js->unlock();

    $css = tcssmerger::i();
    $css->lock();
    $css->deletefile('default', "plugins/$plugindir/resource/enscroll.min.css");
    $css->unlock();
}

}//class