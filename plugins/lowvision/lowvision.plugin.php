<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class lowvision extends tplugin {

  public static function i() {
    return getinstance(__class__);
  }

  public function install() {
    $plugindir = basename(dirname(__file__));
    tjsmerger::i()->add('default', "plugins/$plugindir/resource/lowvision.min.js");
    tcssmerger::i()->add('default', "plugins/$plugindir/resource/lowvision.min.css");

    $about = tplugins::getabout(basename(dirname(__file__)));
    $this->data['idwidget'] = tcustomwidget::i()->add(1, $about['title'], file_get_contents(dirname(__file__) . '/resource/widget.html') , 'widget');
    $this->save();
  }

  public function uninstall() {
    tcustomwidget::i()->delete($this->idwidget);

    $plugindir = basename(dirname(__file__));
    tjsmerger::i()->deletefile('default', "plugins/$plugindir/resource/lowvision.min.js");
    tcssmerger::i()->deletefile('default', "plugins/$plugindir/resource/lowvision.min.css");
  }

}