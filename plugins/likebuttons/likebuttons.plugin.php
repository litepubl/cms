<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class likebuttons extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['facebook_appid'] = '290433841025058';
  }
  
  public function setfacebook_appid($appid) {
    if (($appid = trim($appid)) && ($appid != $this->facebook_appid)) {
      $this->data['facebook_appid'] = $appid;
      $this->save();
      
      tjsmerger::i()->addtext('default', 'facebook_appid',
      ";ltoptions.facebook_appid='$appid';");
    }
  }
  
}//class