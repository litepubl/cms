<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tbackup2email extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['idcron'] = 0;
  }
  
  public function send() {
    $backuper = tbackuper::i();
    $filename  = $backuper->createbackup();
    
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $ini = parse_ini_file($dir . 'about.ini');
    
    tmailer::SendAttachmentToAdmin("[backup] $filename", $ini['body'], basename($filename), file_get_contents($filename));
  }
  
}//class