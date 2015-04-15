<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tgoogleregservice extends tregservice {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['name'] = 'google';
    $this->data['title'] = 'Google';
    $this->data['icon'] = 'google.png';
    $this->data['url'] = '/google-oauth2callback.php';
  }
  
  public function getauthurl() {
    $url = 'https://accounts.google.com/o/oauth2/auth';
    $url .= '?scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile&';
    $url .= parent::getauthurl();
    return $url;
  }
  
  //handle callback
  public function request($arg) {
    if ($err = parent::request($arg)) return $err;
    $code = $_REQUEST['code'];
    $resp = http::post('https://accounts.google.com/o/oauth2/token', array(
    'code' => $code,
    'client_id' => $this->client_id,
    'client_secret' => $this->client_secret,
    'redirect_uri' => litepublisher::$site->url . $this->url,
    'grant_type' => 'authorization_code'
    ));
    
    if ($resp) {
      $tokens  = json_decode($resp);
      if ($r = http::get('https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $tokens->access_token)) {
        $info = json_decode($r);
        return $this->adduser(array(
        //'uid' => $info->id, session depended
        'service' => $this->name,
        'email' => isset($info->email) ? $info->email : '',
        'name' => $info->name,
        'website' => isset($info->link) ? $info->link : ''
        ), $info);
      }
    }
    
    return $this->errorauth();
  }
  
  protected function getadmininfo($lang) {
    return array(
    'regurl' => 'https://code.google.com/apis/console/',
    'client_id' => $lang->client_id,
    'client_secret' =>$lang->client_secret
    );
  }
  
}//class