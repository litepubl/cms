<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttogglecode extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function install() {
    tjsmerger::i()->add('default', $this->jsfile);
  }
  
  public function uninstall() {
    tjsmerger::i()->deletefile('default', $this->jsfile);
  }
  
  public function getjsfile() {
    return '/plugins/' . basename(dirname(__file__)) . '/togglecode.min.js';
  }
  
}//class