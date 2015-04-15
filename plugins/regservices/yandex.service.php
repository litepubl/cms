<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tyandexregservice extends tregservice {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['name'] = 'yandex';
    $this->data['title'] = 'Yandex';
    $this->data['icon'] = 'yandex.png';
    $this->data['url'] = '/yandex-oauth2callback.php';
  }
  
  public function getauthurl() {
    $url = 'https://oauth.yandex.ru/authorize?response_type=code'.
    $url.= '&client_id=' . $this->client_id;
    $url .= '&state=' . $this->newstate();
    return $url;
  }
  
  //handle callback
  public function request($arg) {
    if ($err = parent::request($arg)) return $err;
    $code = $_REQUEST['code'];
    $resp = http::post('https://oauth.yandex.ru/token', array(
    'code' => $code,
    'client_id' => $this->client_id,
    'client_secret' => $this->client_secret,
    'grant_type' => 'authorization_code'
    ));
    
    if ($resp) {
      $tokens  = json_decode($resp);
      if ($r = http::get('https://login.yandex.ru/info?format=json&oauth_token=' . $tokens->access_token)) {
        $info = json_decode($r);
        return $this->adduser(array(
        'service' => $this->name,
        'uid' => $info->id,
        'email' => isset($info->default_email) ? $info->default_email : $info->emails[0],
        'name' => isset($info->real_name) ? $info->real_name : $info->display_name,
        ), $info);
      }
    }
    
    return $this->errorauth();
  }
  
  protected function getadmininfo($lang) {
    return array(
    'regurl' => 'https://oauth.yandex.ru/client/new',
    'client_id' => $lang->yandex_id,
    'client_secret' =>$lang->yandex_secret
    );
  }
  
}//class