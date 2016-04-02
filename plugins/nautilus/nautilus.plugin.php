<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl\plugins;
use litepubl;

class nautilus_font extends tplugin {

  public static function i() {
    return getinstance(__class__);
  }

  public function install() {
    $plugindir = basename(dirname(__file__));
    tjsmerger::i()->add('default', "plugins/$plugindir/resource/nautilus.min.js");
    tcssmerger::i()->add('default', "plugins/$plugindir/resource/nautilus.min.css");
  }

  public function uninstall() {
    $plugindir = basename(dirname(__file__));
    tjsmerger::i()->deletefile('default', "plugins/$plugindir/resource/nautilus.min.js");
    tcssmerger::i()->deletefile('default', "plugins/$plugindir/resource/nautilus.min.css");
  }

}