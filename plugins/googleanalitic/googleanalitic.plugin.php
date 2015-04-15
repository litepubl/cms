<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tgoogleanalitic extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['user'] = '';
    $this->data['se'] = '';
  }
  
  public function getcontent() {
    $tml = '[text:user]
    [editor:se]';
    $html = tadminhtml::i();
    $args = targs::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.user'] = $about['user'];
    $args->data['$lang.se'] = $about['se'];
    $args->user = $this->user;
    $args->se = $this->se;
    return $html->adminform($tml, $args);
  }
  
  public function processform() {
    $this->user = $_POST['user'];
    $this->se = $_POST['se'];
    $this->save();
    
    $jsmerger = tjsmerger::i();
    if ($this->user == '') {
      $jsmerger->deletetext('default', 'googleanalitic');
    } else {
      $s = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'googleanalitic.js');
      $s = sprintf($s, $this->user, $this->se);
      $jsmerger->addtext('default', 'googleanalitic', $s);
    }
  }
  
  public function install() {
    $this->se = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . litepublisher::$options->language . 'se.js');
    $this->save();
  }
  
  public function uninstall() {
    $jsmerger = tjsmerger::i();
    $jsmerger->deletetext('default', 'googleanalitic');
  }
  
}//class