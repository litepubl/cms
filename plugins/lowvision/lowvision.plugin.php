<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class lowvision extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function install() {
    $plugindir = basename(dirname(__file__));
    $js = tjsmerger::i();
    $js->lock();
    $js->add('default', "plugins/$plugindir/resource/thumbnails.min.js");
    $js->unlock();
    
    $css = tcssmerger::i();
    $css->lock();
    $css->add('default', "plugins/$plugindir/resource/thumbnails.min.css");
    $css->add('admin', "plugins/$plugindir/resource/admin.thumbnails.min.css");
    $css->unlock();

$about = tplugins::getabout(basename(dirname(__file__));
$this->data['idwidget'] = tcustomwidget::i()->add(1, $about['title'], file_get_contents(dirname(__file__) . '/resource/widget.html'), 'widget');
$this->save();
  }
  
  public function uninstall() {
tcustomwidget::i()->delete($this->idwidget);

    $plugindir = basename(dirname(__file__));
    $js = tjsmerger::i();
    $js->lock();
    $js->deletefile('default', "plugins/$plugindir/resource/thumbnails.min.js");
    $js->unlock();
    
    $css = tcssmerger::i();
    $css->lock();
    $css->deletefile('default', "plugins/$plugindir/resource/thumbnails.min.css");
    $css->deletefile('admin', "plugins/$plugindir/resource/admin.thumbnails.min.css");
    $css->unlock();
   }

}//class    
